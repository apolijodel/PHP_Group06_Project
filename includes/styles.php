<?php
/**
 * The stylesheet links, in one place.
 *
 * The design system is split across eight files and the cascade depends on
 * their order, so emitting that list from a single include is the only way to
 * be sure every page loads them the same way. Five templates each keeping
 * their own copy of the list is how one of them ends up a file behind.
 *
 * Each link carries the file's mtime as a cache buster, so an edit is picked
 * up without anyone having to hard-refresh.
 */
$cssDir = __DIR__ . '/../assets/css/';

/** Load order is significant - later files intentionally override earlier. */
$sheets = [
    'variables.css',   // tokens: colour, spacing, radii, shadows
    'base.css',        // element defaults and page layout
    'components.css',  // buttons, forms, cards, badges, tables
    'customer.css',    // the storefront
    'admin.css',       // the back office
    'auth.css',        // sign-in and registration
    'responsive.css',  // breakpoints for all of the above
    'overrides.css',   // integration fixes and QA corrections, kept last
    'dark.css',        // the dark theme, last of all so it wins
];

foreach ($sheets as $sheet):
    $path = $cssDir . $sheet;
    ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/<?= $sheet ?>?v=<?= is_file($path) ? filemtime($path) : '1' ?>">
<?php endforeach; ?>
