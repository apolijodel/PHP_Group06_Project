<?php
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../icons.php';

$pageTitle = $pageTitle ?? 'MarkMe';
// $fullWidth = true lets a page lay out its own full-bleed sections.
$fullWidth = $fullWidth ?? false;

// Prompt guests to log in once per session, the first time they land on the site.
if (!is_logged_in() && empty($_SESSION['welcomed'])) {
    $_SESSION['welcomed'] = true;
    if (empty($_SESSION['flash']['reopen_modal'])) {
        flash_set('reopen_modal', 'login');
    }
}

$flashSuccess = flash_get('success');
$flashError = flash_get('error');
?>
<!DOCTYPE html>
<html lang="en" data-base="<?= BASE_URL ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#fbf8f3">
    <meta name="description" content="MarkMe — handmade, personalized bookmarks. Choose a shape and design, add your photo and your words, preview it, and we'll craft it for you.">
    <title><?= e($pageTitle) ?> · MarkMe</title>
<?php require __DIR__ . '/../theme_boot.php'; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php require __DIR__ . '/../styles.php'; ?>
</head>
<body>
<?php require __DIR__ . '/navbar.php'; ?>
<main id="main"<?= $fullWidth ? '' : ' class="container page-shell"' ?>>
    <?php if ($flashSuccess || $flashError): ?>
        <?php if ($fullWidth): ?><div class="container" style="padding-top:var(--s-5)"><?php endif; ?>
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-start gap-2" role="status">
                <?= icon('check-circle', 20) ?>
                <span class="flex-grow-1"><?= e($flashSuccess) ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss"></button>
            </div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-start gap-2" role="alert">
                <?= icon('alert', 20) ?>
                <span class="flex-grow-1"><?= e($flashError) ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss"></button>
            </div>
        <?php endif; ?>
        <?php if ($fullWidth): ?></div><?php endif; ?>
    <?php endif; ?>
