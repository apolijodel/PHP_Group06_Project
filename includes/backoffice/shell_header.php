<?php
/**
 * The back-office shell markup, shared by the administrator panel and the
 * staff panel.
 *
 * Authorisation is NOT done here - each panel's own header does that before
 * requiring this, because the two panels admit different people. This file
 * is markup only, so there is one copy of the chrome rather than two that
 * drift apart.
 */

// Assigned here, before any markup. The split that created this file
// originally left a PHP close tag above these lines, so they were
// printed as text instead of run and the flash variables were never
// defined. Keep them inside this block.
$pageTitle = $pageTitle ?? 'Admin';
$flashSuccess = flash_get('success');
$flashError = flash_get('error');
?>
<!DOCTYPE html>
<html lang="en" data-base="<?= BASE_URL ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?> · MarkMe Admin</title>
<?php require __DIR__ . '/../../includes/theme_boot.php'; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php require __DIR__ . '/../../includes/styles.php'; ?>
</head>
<body class="admin-body">
<div class="admin-shell">
    <?php require __DIR__ . '/../../includes/backoffice/sidebar.php'; ?>
    <div class="admin-scrim" id="adminScrim"></div>

    <div class="admin-main">
        <header class="admin-top">
            <button type="button" class="btn-icon admin-burger" id="adminBurger"
                    aria-expanded="false" aria-controls="adminSide" aria-label="Open admin menu">
                <?= icon('menu') ?>
            </button>
            <h1><?= e($pageTitle) ?></h1>
            <div class="admin-top-actions">
                <a class="btn btn-secondary btn-sm" href="<?= BASE_URL ?>/customer/index.php">
                    <?= icon('external', 15) ?> <span class="d-none d-sm-inline">View site</span>
                </a>
                <?php
                /* The account menu, same component the storefront header uses
                   so there is one dropdown to keep working rather than two.
                   Settings is filtered by capability. Both roles reach it,
                   but panel_url sends each to their own panel's page: the
                   administrator's configures the shop, the staff one carries
                   only the personal settings they are allowed to change.

                   Everything belonging to the person signed in lives here:
                   their profile, their settings, and the way out. */
                $accountLinks = array_values(array_filter([
                    ['profile.php', 'user', 'Profile', 'profile.self'],
                    ['settings.php', 'settings', 'Settings', 'settings.appearance'],
                ], static fn($link) => can($link[3])));
                ?>
                <div class="dropdown account-menu">
                    <button type="button" class="header-user" id="adminAccountBtn"
                            data-bs-toggle="dropdown" data-bs-offset="0,8" aria-expanded="false">
                        <span class="avatar" aria-hidden="true"><?= e(initials(current_user()['full_name'])) ?></span>
                        <span class="name"><?= e(current_user()['username']) ?></span>
                        <span class="caret" aria-hidden="true"><?= icon('chevron-down', 15) ?></span>
                        <span class="visually-hidden">Open account menu</span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end account-dropdown"
                         aria-labelledby="adminAccountBtn">
                        <div class="account-dropdown-id">
                            <span class="avatar" aria-hidden="true"><?= e(initials(current_user()['full_name'])) ?></span>
                            <span class="who">
                                <strong><?= e(current_user()['full_name']) ?></strong>
                                <span><?= e(current_user()['email']) ?></span>
                            </span>
                        </div>

                        <?php foreach ($accountLinks as [$file, $ico, $label, $cap]): ?>
                            <a class="dropdown-item" href="<?= panel_url($file) ?>">
                                <?= icon($ico, 17) ?> <?= e($label) ?>
                            </a>
                        <?php endforeach; ?>

                        <hr class="dropdown-divider">
                        <a class="dropdown-item is-logout" href="<?= panel_url('logout.php') ?>">
                            <?= icon('logout', 17) ?> Log out
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <main class="admin-content">
            <?php if ($flashSuccess): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-start gap-2"
                     role="status" style="margin-bottom:var(--s-6)">
                    <?= icon('check-circle', 20) ?>
                    <span class="flex-grow-1"><?= e($flashSuccess) ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss"></button>
                </div>
            <?php endif; ?>
            <?php if ($flashError): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-start gap-2"
                     role="alert" style="margin-bottom:var(--s-6)">
                    <?= icon('alert', 20) ?>
                    <span class="flex-grow-1"><?= e($flashError) ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss"></button>
                </div>
            <?php endif; ?>
