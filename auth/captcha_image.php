<?php
/**
 * The CAPTCHA image itself.
 *
 * Self-hosted and real: the answer lives only in the session, never in the
 * HTML, and captcha_local_verify() consumes it so one image cannot be
 * replayed. This exists because reCAPTCHA needs keys from Google that this
 * environment does not have — rather than leaving registration unprotected
 * and pretending otherwise.
 *
 * Drawn with GD's built-in font: each character is rendered on its own small
 * canvas, rotated, and composited at a jittered baseline, which distorts the
 * text without needing a TTF file shipped with the project.
 */
require_once __DIR__ . '/../includes/security.php';

$code = captcha_local_issue();

$w = 190;
$h = 60;

$img = imagecreatetruecolor($w, $h);
imagealphablending($img, true);

// MarkMe's cream, so the control does not look pasted in from another site.
$bg = imagecolorallocate($img, 250, 246, 240);
imagefilledrectangle($img, 0, 0, $w, $h, $bg);

// Speckle and arcs first, so they sit behind the characters.
for ($i = 0; $i < 420; $i++) {
    $dot = imagecolorallocatealpha($img, random_int(150, 210), random_int(140, 200), random_int(130, 190), 70);
    imagesetpixel($img, random_int(0, $w - 1), random_int(0, $h - 1), $dot);
}
for ($i = 0; $i < 4; $i++) {
    $line = imagecolorallocatealpha($img, random_int(170, 215), random_int(150, 195), random_int(140, 185), 60);
    imagearc(
        $img,
        random_int(0, $w),
        random_int(0, $h),
        random_int(40, 140),
        random_int(20, 70),
        0,
        360,
        $line
    );
}

$chars = str_split($code);
$slot = (int)floor(($w - 24) / max(1, count($chars)));

foreach ($chars as $i => $char) {
    // Each character is drawn small on its own canvas, scaled up, then
    // rotated. Scaling GD's bitmap font rather than using imagettftext keeps
    // this working without shipping a TTF, and the interpolation softens the
    // edges into something that reads as type rather than pixels.
    $tile = imagecreatetruecolor(12, 18);
    $tileBg = imagecolorallocate($tile, 250, 246, 240);
    imagefilledrectangle($tile, 0, 0, 12, 18, $tileBg);

    // Ink in the brand's two darks, varied per character.
    $ink = random_int(0, 1) === 0
        ? imagecolorallocate($tile, 47, 42, 36)
        : imagecolorallocate($tile, 150, 62, 24);

    imagestring($tile, 5, 1, 1, $char, $ink);

    $scale = random_int(24, 30) / 10;
    $big = imagescale($tile, (int)round(12 * $scale), (int)round(18 * $scale));
    imagecolortransparent($big, imagecolorclosest($big, 250, 246, 240));

    $rotated = imagerotate($big, random_int(-22, 22), imagecolorallocate($big, 250, 246, 240));
    imagecolortransparent($rotated, imagecolorclosest($rotated, 250, 246, 240));

    imagecopy(
        $img,
        $rotated,
        10 + $i * $slot,
        (int)(($h - imagesy($rotated)) / 2) + random_int(-5, 5),
        0,
        0,
        imagesx($rotated),
        imagesy($rotated)
    );

    imagedestroy($tile);
    imagedestroy($big);
    imagedestroy($rotated);
}

// One stroke over the top, so the glyphs are not cleanly separable. More than
// that starts costing honest people more than it costs a script.
$stroke = imagecolorallocatealpha($img, 150, 62, 24, 88);
imagesetthickness($img, 2);
imageline($img, 0, random_int(16, $h - 16), $w, random_int(16, $h - 16), $stroke);

// Never cached: a cached image would keep answering with a consumed code.
header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

imagepng($img);
imagedestroy($img);
