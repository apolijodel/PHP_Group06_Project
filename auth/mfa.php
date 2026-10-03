<?php
/**
 * Multi-factor authentication challenge.
 *
 * Reached only with a half-open session: attempt_login() verified the password
 * and set $_SESSION['mfa_pending'], but deliberately did NOT set
 * $_SESSION['user']. Until the code below checks out, every require_login()
 * and require_can() in the application still sees a guest - so this page is a
 * real gate, not a screen you can skip by typing another URL.
 *
 * The code is verified against the account's stored TOTP secret with
 * totp_verify() (RFC 6238). Codes are never written to the audit log.
 */
require_once __DIR__ . '/../includes/auth.php';

$pending = mfa_pending();

if (!$pending) {
    // No challenge in flight - either it expired or somebody came here direct.
    flash_set('login_error', 'Your sign-in attempt expired. Please log in again.');
    flash_set('reopen_modal', 'login');
    redirect('/customer/index.php');
}

$returnTo = safe_local_path($_GET['return'] ?? $_POST['return_to'] ?? null);
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $code = trim($_POST['code'] ?? '');

    $stmt = db()->prepare('SELECT * FROM users WHERE user_id = :id AND is_active = 1');
    $stmt->execute(['id' => $pending['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        unset($_SESSION['mfa_pending']);
        flash_set('login_error', 'That account is no longer available.');
        flash_set('reopen_modal', 'login');
        redirect('/customer/index.php');
    }

    if (totp_verify((string)$user['mfa_secret'], $code)) {
        establish_session($user);

        $destination = in_array($user['role'], ['admin', 'staff'], true)
            ? '/admin/dashboard.php'
            : $returnTo;

        redirect($destination);
    }

    // A wrong code is a failed authentication attempt like any other, and
    // counts towards the same lockout the password uses.
    $result = register_failed_attempt($user);

    log_activity(
        'auth.mfa_failed',
        'Incorrect authenticator code (attempt ' . $result['attempts'] . ' of ' . sec_max_attempts() . ')',
        'user',
        (int)$user['user_id'],
        $user,
        'Authentication'
    );

    if ($result['locked']) {
        log_activity(
            'account.locked',
            'Account locked after ' . $result['attempts'] . ' failed authentication attempts',
            'user',
            (int)$user['user_id'],
            $user,
            'Authentication'
        );
        unset($_SESSION['mfa_pending']);
        flash_set('login_error', 'This account is now locked. An administrator must unlock it.');
        flash_set('reopen_modal', 'login');
        redirect('/customer/index.php');
    }

    $error = 'That code is not correct. Check your authenticator app and try again.';
}

$pageTitle = 'Two-factor authentication';
?>
<!DOCTYPE html>
<html lang="en" data-base="<?= BASE_URL ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Two-factor authentication · MarkMe</title>
<?php require __DIR__ . '/../includes/theme_boot.php'; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php require __DIR__ . '/../includes/styles.php'; ?>
</head>
<body class="admin-login-body">
<main class="admin-login-card">
    <span class="brand-mark">MarkMe</span>

    <h1 style="font-size:1.35rem;margin-bottom:var(--s-2)">Enter your code</h1>
    <p class="text-muted" style="font-size:.92rem;margin-bottom:var(--s-6)">
        Signing in as <strong><?= e($pending['username']) ?></strong>. Open your authenticator
        app and enter the current 6-digit code.
    </p>

    <?php if ($error): ?>
        <div class="alert alert-danger d-flex gap-2 align-items-start" role="alert">
            <?= icon('alert', 18) ?><span><?= e($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
        <div class="field-group">
            <label class="form-label" for="mfaCode">Authentication code</label>
            <input type="text" id="mfaCode" name="code" class="form-control mono-num"
                   inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}"
                   maxlength="6" required autofocus
                   style="letter-spacing:.4em;text-align:center;font-size:1.3rem">
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block">
            <?= icon('shield', 17) ?> Verify
        </button>
    </form>

    <p style="text-align:center;margin-top:var(--s-6);font-size:.88rem">
        <a href="<?= BASE_URL ?>/customer/logout.php">Cancel and sign out</a>
    </p>
</main>
</body>
</html>
