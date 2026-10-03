<?php
/**
 * Settings - staff panel.
 *
 * Only the part of Settings that belongs to the person signed in. Store
 * information and system preferences configure the shop, so they stay with
 * the administrators who are accountable for them; the theme is a personal,
 * per-device choice, and before this screen existed staff had no way to
 * switch it from inside their own panel at all.
 *
 * The capability is checked by the shell below and again by nothing else,
 * because there is nothing else here to protect - the panel writes no
 * settings, it only hosts a control that lives entirely in the browser.
 */
$requireCapability = 'settings.appearance';
$pageTitle = 'Settings';
require __DIR__ . '/includes/staff_header.php';
?>

<div class="page-head">
    <div>
        <p class="eyebrow eyebrow-muted">System</p>
        <h2 style="margin-top:var(--s-2)">Settings</h2>
        <p>Your preferences on this device.</p>
    </div>
</div>

<?php require __DIR__ . '/../includes/backoffice/appearance_panel.php'; ?>

<p class="form-text" style="margin-top:var(--s-5)">
    Store details, registration and stock defaults are managed by an administrator.
</p>

<?php require __DIR__ . '/includes/staff_footer.php'; ?>
