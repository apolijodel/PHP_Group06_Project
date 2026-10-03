<?php
/**
 * Settings is deliberately a separate page from Profile.
 *
 * Profile owns who the customer is and where we deliver. Settings owns how the
 * site behaves for them: appearance, password, and session. No personal or
 * address field appears here, and nothing from Profile is duplicated.
 */
require_once __DIR__ . '/../includes/auth.php';
require_login();

$userId = current_user()['user_id'];
$stmt = db()->prepare('SELECT * FROM users WHERE user_id = :id');
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

$pwErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    csrf_check();
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $user['password_hash'])) {
        $pwErrors[] = 'Current password is incorrect.';
    }
    foreach (password_policy_errors($new) as $missing) {
        $pwErrors[] = 'New password needs: ' . $missing . '.';
    }
    if ($new === $current && $new !== '') {
        $pwErrors[] = 'The new password must be different from the current one.';
    }
    if ($new !== $confirm) {
        $pwErrors[] = 'New password confirmation does not match.';
    }
    // Rotating between two familiar passwords defeats the point of expiry.
    if (!$pwErrors && password_was_used_before($userId, $new)) {
        $pwErrors[] = 'You have used that password recently. Please choose a different one.';
    }

    if (!$pwErrors) {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        db()->prepare('UPDATE users SET password_hash = :hash WHERE user_id = :id')
            ->execute(['hash' => $hash, 'id' => $userId]);

        password_record_change($userId, $hash);
        unset($_SESSION['password_expired']);

        // A password change is a good moment to re-key the session, so a token
        // captured earlier cannot keep riding the old id.
        session_regenerate_id(true);

        log_activity('account.password', 'Changed their password', 'user', $userId, null, 'Security');

        flash_set('success', 'Password changed successfully.');
        redirect('/customer/settings.php');
    }
}

$sessionStarted = $_SESSION['started_at'] ?? null;

$pageTitle = 'Settings';
require __DIR__ . '/../includes/customer/header.php';
?>

<div class="account-page">
    <div class="d-flex flex-column" style="gap:var(--s-6)">
        <div class="page-head" style="margin-bottom:0">
            <div>
                <p class="eyebrow">Account</p>
                <h1 style="margin-top:var(--s-2)">Settings</h1>
                <p>How MarkMe looks and how your account is secured. Your details and delivery
                    address are in <a href="<?= BASE_URL ?>/customer/profile.php">Profile</a>.</p>
            </div>
        </div>

        <!-- --------------------------- appearance --------------------------- -->
        <section class="card card-flush">
            <div class="panel-head">
                <h2 class="d-flex align-items-center gap-2"><?= icon('sun', 18) ?> Appearance</h2>
            </div>
            <div class="panel-body">
                <div class="setting-row">
                    <div>
                        <p class="setting-title">Theme</p>
                        <p class="form-text" style="margin:0">Choose how MarkMe looks on this device.
                            Your choice is remembered the next time you visit.</p>
                    </div>
                    <div class="theme-toggle" role="group" aria-label="Colour theme">
                        <button type="button" data-theme-option="light" aria-pressed="true">
                            <?= icon('sun', 16) ?> Light
                        </button>
                        <button type="button" data-theme-option="dark" aria-pressed="false">
                            <?= icon('moon', 16) ?> Dark
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ---------------------------- account ----------------------------- -->
        <section class="card card-flush" id="password">
            <div class="panel-head">
                <h2 class="d-flex align-items-center gap-2"><?= icon('key', 18) ?> Change password</h2>
            </div>
            <div class="panel-body">
                <?php if ($pwErrors): ?>
                    <div class="alert alert-danger d-flex gap-2 align-items-start" role="alert">
                        <?= icon('alert', 20) ?>
                        <ul style="margin:0">
                            <?php foreach ($pwErrors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-grid">
                        <div class="span-2">
                            <label class="form-label" for="current_password">Current password</label>
                            <input type="password" id="current_password" name="current_password"
                                   class="form-control" autocomplete="current-password" required>
                        </div>
                        <div>
                            <label class="form-label" for="new_password">New password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control"
                                   minlength="<?= sec_password_min() ?>" autocomplete="new-password" required
                                   data-password-input aria-describedby="setPolicy">
                            <ul class="pw-policy" id="setPolicy" data-password-policy aria-live="polite">
                                <?php foreach (array_keys(password_rules()) as $rule): ?>
                                    <li data-rule="<?= e($rule) ?>">
                                        <span class="pw-mark" aria-hidden="true"></span><?= e($rule) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div>
                            <label class="form-label" for="confirm_password">Confirm new password</label>
                            <input type="password" id="confirm_password" name="confirm_password"
                                   class="form-control" minlength="<?= sec_password_min() ?>"
                                   autocomplete="new-password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top:var(--s-5)">
                        <?= icon('key', 16) ?> Change password
                    </button>
                </form>
            </div>
        </section>

        <!-- ------------------------------ MFA ------------------------------- -->
        <?php require __DIR__ . '/../includes/mfa_panel.php'; ?>

        <!-- ---------------------------- session ----------------------------- -->
        <section class="card card-flush">
            <div class="panel-head">
                <h2 class="d-flex align-items-center gap-2"><?= icon('shield', 18) ?> Session</h2>
            </div>
            <div class="panel-body">
                <div class="setting-row">
                    <div>
                        <p class="setting-title">Signed in as <?= e($user['username']) ?></p>
                        <p class="form-text" style="margin:0">
                            <?php if ($sessionStarted): ?>
                                Signed in <?= e(date('M j, Y \a\t g:i A', $sessionStarted)) ?>.
                            <?php endif; ?>
                            Signing out ends this session on this device.
                        </p>
                    </div>
                    <a href="<?= BASE_URL ?>/customer/logout.php" class="btn btn-secondary btn-sm">
                        <?= icon('logout', 15) ?> Log out
                    </a>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require __DIR__ . '/../includes/customer/footer.php'; ?>
