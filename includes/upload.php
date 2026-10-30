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

/**
 * Scan a file using Cloudmersive Virus Scan API.
 *
 * Returns:
 * [
 *     'scanned' => bool,
 *     'clean'   => bool,
 *     'detail'  => string
 * ]
 *
 * Fail closed: API errors, missing API keys, invalid responses,
 * and timeouts are all treated as scan failures.
 */
function av_scan(string $path): array
{
    $apiKey = trim((string)getenv('CLOUDMERSIVE_API_KEY'));

    if ($apiKey === '') {
        error_log('av_scan: CLOUDMERSIVE_API_KEY is not configured.');
        return [
            'scanned' => false,
            'clean' => false,
            'detail' => 'API key not configured.'
        ];
    }

    if (!function_exists('curl_init')) {
        error_log('av_scan: PHP cURL extension is unavailable.');
        return [
            'scanned' => false,
            'clean' => false,
            'detail' => 'cURL extension unavailable.'
        ];
    }

    if (!is_file($path) || !is_readable($path)) {
        return [
            'scanned' => false,
            'clean' => false,
            'detail' => 'File is missing or unreadable.'
        ];
    }

    // Cloudmersive Free Tier maximum file size: 3.5 MB.
    $maxApiBytes = (int)(3.5 * 1024 * 1024);

    if (filesize($path) > $maxApiBytes) {
        return [
            'scanned' => false,
            'clean' => false,
            'detail' => 'File exceeds API size limit.'
        ];
    }

    try {
        $mime = mime_content_type($path) ?: 'application/octet-stream';

        $file = new CURLFile(
            $path,
            $mime,
            basename($path)
        );

        $ch = curl_init(
            'https://api.cloudmersive.com/virus/scan/file'
        );

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'inputFile' => $file
            ],
            CURLOPT_HTTPHEADER => [
                'Apikey: ' . $apiKey,
                'Accept: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($response === false) {
            error_log('av_scan: Cloudmersive request failed: ' . $curlError);

            return [
                'scanned' => false,
                'clean' => false,
                'detail' => 'API request failed.'
            ];
        }

        if ($httpCode !== 200) {
            error_log(
                'av_scan: Cloudmersive returned HTTP ' . $httpCode
            );

            return [
                'scanned' => false,
                'clean' => false,
                'detail' => 'API returned an unsuccessful status.'
            ];
        }

        $result = json_decode($response, true);

        if (
            !is_array($result) ||
            !array_key_exists('CleanResult', $result) ||
            !is_bool($result['CleanResult'])
        ) {
            error_log('av_scan: Invalid Cloudmersive response.');

            return [
                'scanned' => false,
                'clean' => false,
                'detail' => 'Invalid API response.'
            ];
        }

        if ($result['CleanResult'] === true) {
            return [
                'scanned' => true,
                'clean' => true,
                'detail' => 'Cloudmersive found no threats.'
            ];
        }

        error_log(
            'av_scan: Cloudmersive detected a threat. '
            . 'FoundViruses: '
            . json_encode($result['FoundViruses'] ?? [])
        );

        return [
            'scanned' => true,
            'clean' => false,
            'detail' => 'Malware or unsafe content detected.'
        ];

    } catch (Throwable $e) {
        error_log('av_scan: ' . $e->getMessage());

        return [
            'scanned' => false,
            'clean' => false,
            'detail' => 'Scanner error.'
        ];
    }
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
    /* A 1x1 pixel is a perfectly valid PNG and a useless bookmark. The floor
       is what makes "it is an image" mean "it could actually be printed"; the
       ceiling keeps a decompression bomb out of the uploads directory. */
    if ($size[0] < MIN_UPLOAD_PIXELS || $size[1] < MIN_UPLOAD_PIXELS) {
        throw new RuntimeException(
            'That image is only ' . $size[0] . ' by ' . $size[1] . ' pixels. '
            . 'Artwork must be at least ' . MIN_UPLOAD_PIXELS . ' pixels on each side.'
        );
    }
    if ($size[0] > 6000 || $size[1] > 6000) {
        throw new RuntimeException('Image dimensions must be 6000 pixels or fewer on each side.');
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
