<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Scan a file for malware before it is accepted.
 *
 * Returns ['scanned' => bool, 'clean' => bool, 'detail' => string]. The caller
 * must look at 'scanned': this function never pretends a check happened. With
 * no scanner configured it says so and leaves the policy decision (refuse, or
 * accept unscanned) to av_guard() below.
 *
 * ClamAV exit codes: 0 = clean, 1 = infected, anything else = the scanner
 * itself failed, which is treated as "not scanned" rather than "clean".
 */
function av_scan(string $path): array
{
    $scanner = defined('AV_SCANNER') ? trim((string)AV_SCANNER) : '';
    if ($scanner === '') {
        return ['scanned' => false, 'clean' => false, 'detail' => 'No scanner configured.'];
    }
    if (!is_file($scanner) && !is_executable($scanner)) {
        error_log('av_scan: AV_SCANNER is set but not usable: ' . $scanner);
        return ['scanned' => false, 'clean' => false, 'detail' => 'Scanner not found.'];
    }

    $timeout = defined('AV_TIMEOUT_SECONDS') ? (int)AV_TIMEOUT_SECONDS : 20;
    $cmd = escapeshellarg($scanner) . ' --no-summary ' . escapeshellarg($path);

    $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $proc = @proc_open($cmd, $descriptors, $pipes);
    if (!is_resource($proc)) {
        error_log('av_scan: could not start the scanner');
        return ['scanned' => false, 'clean' => false, 'detail' => 'Scanner could not start.'];
    }

    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);
    $out = '';
    $deadline = time() + max(1, $timeout);
    do {
        $out .= (string)stream_get_contents($pipes[1]);
        $out .= (string)stream_get_contents($pipes[2]);
        $status = proc_get_status($proc);
        if (!$status['running']) {
            break;
        }
        usleep(100000);
    } while (time() < $deadline);

    if ($status['running']) {
        proc_terminate($proc);
        proc_close($proc);
        error_log('av_scan: scanner timed out');
        return ['scanned' => false, 'clean' => false, 'detail' => 'Scanner timed out.'];
    }

    foreach ($pipes as $pipe) {
        if (is_resource($pipe)) {
            fclose($pipe);
        }
    }
    $code = $status['exitcode'];
    proc_close($proc);

    if ($code === 0) {
        return ['scanned' => true, 'clean' => true, 'detail' => 'No threat found.'];
    }
    if ($code === 1) {
        return ['scanned' => true, 'clean' => false, 'detail' => 'Malware signature detected.'];
    }

    error_log('av_scan: scanner returned ' . $code . ' - ' . trim($out));
    return ['scanned' => false, 'clean' => false, 'detail' => 'Scanner error.'];
}

/**
 * Apply the configured scanning policy, throwing if the file must not be kept.
 * Returns true when a scan actually ran and passed, false when none ran and
 * the configuration permits that — the caller records which it was.
 */
function av_guard(string $path): bool
{
    $result = av_scan($path);

    if ($result['scanned'] && !$result['clean']) {
        throw new RuntimeException('That file was rejected by the malware scanner.');
    }
    if (!$result['scanned'] && (defined('AV_REQUIRED') && AV_REQUIRED)) {
        throw new RuntimeException(
            'Uploads are unavailable: the malware scanner is not responding. '
            . 'Please try again later.'
        );
    }

    return $result['scanned'];
}

/**
 * Validate and move an uploaded image into $targetDir.
 * Returns the stored filename on success, or throws RuntimeException with a
 * user-facing message on failure.
 *
 * $scanned is set by reference to whether a malware scan actually ran, so the
 * caller can record the truth rather than an assumption.
 */
function handle_image_upload(array $file, string $targetDir, ?bool &$scanned = null): string
{
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Invalid upload parameters.');
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        throw new RuntimeException('No file was uploaded.');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed. Please try again.');
    }

    if ($file['size'] > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('Image is too large. Maximum size is 2MB.');
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Invalid upload.');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    /* SVG is refused deliberately, and says so.

       An SVG is XML, not pixels: it can carry <script>, event handlers, and
       references that fetch from other sites, and it is rendered by the
       browser rather than decoded as an image. Accepting one means either
       shipping a sanitiser that has to be right about every XML trick, or
       serving attacker-controlled markup from our own origin. The catalog
       does not need it - the shapes and designs seeded with the project are
       SVG files placed by a developer, not uploaded through this form. */
    if ($ext === 'svg' || $ext === 'svgz') {
        throw new RuntimeException(
            'SVG files are not accepted, because they can contain scripts. '
            . 'Please upload a JPG, PNG or WEBP image instead.'
        );
    }

    if (!in_array($ext, ALLOWED_IMAGE_EXT, true)) {
        throw new RuntimeException('Unsupported file type. Allowed: JPG, PNG and WEBP.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ALLOWED_IMAGE_MIME, true)) {
        throw new RuntimeException('File does not appear to be a valid image.');
    }

    /* The extension and the content have to agree. Checked separately they
       both pass for a file called photo.png that actually contains JPEG, and
       the name is what the file is finally served as. */
    $mimeForExt = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png', 'webp' => 'image/webp',
    ];
    if (($mimeForExt[$ext] ?? null) !== $mime) {
        throw new RuntimeException(
            'That file\'s contents do not match its ' . strtoupper($ext) . ' extension.'
        );
    }

    $size = @getimagesize($file['tmp_name']);
    if ($size === false) {
        throw new RuntimeException('File does not appear to be a valid image.');
    }

    // Decoding it proves it is an image; it also stops a "decompression bomb"
    // from being stored and later opened by something that tries to render it.
    if ($size[0] < 1 || $size[1] < 1 || $size[0] > 6000 || $size[1] > 6000) {
        throw new RuntimeException('Image dimensions must be between 1 and 6000 pixels.');
    }

    // Malware check before anything is written to a served directory.
    $scanned = av_guard($file['tmp_name']);

    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
        throw new RuntimeException('Server error: unable to prepare upload directory.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $destination = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Server error: unable to save the uploaded file.');
    }

    return $filename;
}

/**
 * Duplicate an already-stored upload under a fresh random filename.
 *
 * Used when a saved design is added to the cart. The cart/order line takes its
 * own copy of the photo rather than pointing at the saved design's file, so the
 * two have independent lifetimes: editing or deleting a saved design can never
 * pull the image out from under an order that already referenced it. This is
 * the same snapshot rule order_items already applies to names and prices.
 *
 * Returns the new filename, or null when the source is missing.
 */
function copy_stored_upload(string $dir, ?string $filename): ?string
{
    if (!$filename) {
        return null;
    }

    $source = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . basename($filename);
    if (!is_file($source)) {
        return null;
    }

    $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_IMAGE_EXT, true)) {
        return null;
    }

    $copy = bin2hex(random_bytes(16)) . '.' . $ext;
    $destination = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $copy;

    return copy($source, $destination) ? $copy : null;
}
