<?php
/**
 * Admin settings — store-wide configuration, separate from Admin Profile.
 *
 * Everything on this page is persisted in the `settings` table and read back by
 * the storefront, so nothing here is a control that does nothing. The theme
 * choice is the exception, and it is stored per-device in localStorage.
 */
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$errors = [];

// Only these keys may be written from this form.
$editable = [
    'store_name' => 'Store name',
    'store_tagline' => 'Tagline',
    'store_email' => 'Contact email',
    'store_phone' => 'Contact phone',
    'store_address' => 'Store address',
    'low_stock_default' => 'Default low-stock threshold',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_settings') {
    csrf_check();

    $values = [];
    foreach ($editable as $key => $label) {
        $values[$key] = trim($_POST[$key] ?? '');
    }

    if ($values['store_name'] === '') {
        $errors[] = 'Store name is required.';
    }
    if ($values['store_email'] !== '' && !filter_var($values['store_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Contact email must be a valid email address.';
    }
    if ($values['low_stock_default'] !== '' && !ctype_digit($values['low_stock_default'])) {
        $errors[] = 'Default low-stock threshold must be a whole number.';
    }

    if (!$errors) {
        foreach ($values as $key => $value) {
            setting_put($key, $value);
        }
        flash_set('success', 'Store settings saved.');
        redirect('/admin/settings.php');
    }
}

// Applying the default threshold to every product is an explicit, separate
// action — saving the settings form alone must not rewrite catalog data.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'apply_threshold') {
    csrf_check();
    $default = (int)setting('low_stock_default', '5');
    $stmt = db()->prepare('UPDATE products SET low_stock_threshold = :t');
    $stmt->execute(['t' => max(0, $default)]);

    flash_set('success', 'Applied a threshold of ' . max(0, $default) . ' to all products.');
    redirect('/admin/settings.php');
}

$allowRegistration = setting('allow_registration', '1') === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_registration') {
    csrf_check();
    setting_put('allow_registration', $allowRegistration ? '0' : '1');
    flash_set('success', $allowRegistration
        ? 'New customer registration is now closed.'
        : 'New customer registration is now open.');
    redirect('/admin/settings.php');
}

$requireCapability = 'settings.manage';
$pageTitle = 'Settings';
require __DIR__ . '/includes/admin_header.php';
?>

<div class="page-head">
    <div>
        <p class="eyebrow eyebrow-muted">System</p>
        <h2 style="margin-top:var(--s-2)">Settings</h2>
        <p>Store-wide configuration. Your own account details are in
            <a href="<?= BASE_URL ?>/admin/profile.php">Profile</a>.</p>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger d-flex gap-2 align-items-start" role="alert"
         style="margin-bottom:var(--s-5)">
        <?= icon('alert', 20) ?>
        <ul style="margin:0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="d-flex flex-column" style="gap:var(--s-6)">
    <!-- ------------------------ store information ------------------------ -->
    <section class="card card-flush">
        <div class="panel-head">
            <h2 class="d-flex align-items-center gap-2"><?= icon('store', 18) ?> Store information</h2>
        </div>
        <div class="panel-body">
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save_settings">
                <div class="form-grid">
                    <div>
                        <label class="form-label" for="store_name">Store name</label>
                        <input type="text" id="store_name" name="store_name" class="form-control"
                               value="<?= e(setting('store_name', 'MarkMe')) ?>" maxlength="100" required>
                    </div>
                    <div>
                        <label class="form-label" for="store_tagline">Tagline</label>
                        <input type="text" id="store_tagline" name="store_tagline" class="form-control"
                               value="<?= e(setting('store_tagline')) ?>" maxlength="150">
                    </div>
                    <div>
                        <label class="form-label" for="store_email">Contact email</label>
                        <input type="email" id="store_email" name="store_email" class="form-control"
                               value="<?= e(setting('store_email')) ?>" maxlength="150">
                    </div>
                    <div>
                        <label class="form-label" for="store_phone">Contact phone</label>
                        <input type="tel" id="store_phone" name="store_phone" class="form-control"
                               value="<?= e(setting('store_phone')) ?>" maxlength="30">
                    </div>
                    <div class="span-2">
                        <label class="form-label" for="store_address">Store address</label>
                        <input type="text" id="store_address" name="store_address" class="form-control"
                               value="<?= e(setting('store_address')) ?>" maxlength="255">
                    </div>
                    <div>
                        <label class="form-label" for="low_stock_default">Default low-stock threshold</label>
                        <input type="number" id="low_stock_default" name="low_stock_default"
                               class="form-control" value="<?= e(setting('low_stock_default', '5')) ?>"
                               min="0" max="10000" step="1">
                        <p class="form-text">Used for new products. Existing products keep their own
                            threshold, editable in <a href="<?= BASE_URL ?>/admin/inventory.php">Inventory</a>.</p>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:var(--s-5)">
                    <?= icon('check', 16) ?> Save settings
                </button>
            </form>
        </div>
    </section>

    <!-- --------------------------- appearance ---------------------------- -->
    <?php require __DIR__ . '/../includes/backoffice/appearance_panel.php'; ?>

    <!-- ------------------------ system preferences ----------------------- -->
    <section class="card card-flush">
        <div class="panel-head">
            <h2 class="d-flex align-items-center gap-2"><?= icon('settings', 18) ?> System preferences</h2>
        </div>
        <div class="panel-body">
            <div class="setting-row">
                <div>
                    <p class="setting-title">Customer registration</p>
                    <p class="form-text" style="margin:0">
                        <?= $allowRegistration
                            ? 'Anyone can create a customer account from the storefront.'
                            : 'The register form is closed. Existing customers can still sign in.' ?>
                    </p>
                </div>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle_registration">
                    <button type="submit" class="btn btn-secondary btn-sm">
                        <?= $allowRegistration ? 'Close registration' : 'Open registration' ?>
                    </button>
                </form>
            </div>

            <div class="setting-row">
                <div>
                    <p class="setting-title">Apply default threshold to all products</p>
                    <p class="form-text" style="margin:0">Overwrites every product's low-stock threshold with
                        the default above. This cannot be undone.</p>
                </div>
                <form method="post"
                      data-confirm
                  data-confirm-title="Apply to every product?"
                  data-confirm-body="This overwrites the low-stock threshold on all products."
                  data-confirm-action="Apply to all">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="apply_threshold">
                    <button type="submit" class="btn btn-secondary btn-sm">
                        <?= icon('refresh', 15) ?> Apply to all
                    </button>
                </form>
            </div>
        </div>
    </section>
</div>

<?php require __DIR__ . '/../includes/backoffice/shell_footer.php'; ?>
