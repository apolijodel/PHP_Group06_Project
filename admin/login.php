<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/icons.php';

if (is_logged_in() && is_manager()) {
    redirect('/admin/dashboard.php');
}

// A session that timed out, or a guard that bounced the request here, leaves
// its explanation in a flash rather than a query string.
$error = flash_get('session_expired') ?? flash_get('login_error');
flash_get('error');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } else {
        $result = attempt_login($identifier, $password);
        $user = $result['user'];

        if ($result['status'] === 'mfa_required') {
            redirect('/auth/mfa.php?return=' . urlencode('/admin/dashboard.php'));
        } elseif ($result['status'] === 'ok' && in_array($user['role'], ['admin', 'staff'], true)) {
            redirect(panel_base() . '/dashboard.php');
        } elseif ($result['status'] === 'ok') {
            // A customer signing in here would otherwise be left holding a
            // session for a panel they cannot use.
            logout_user();
            $error = 'This account does not have management access.';
        } else {
            // Lockout and not-activated carry their own explanation; a plain
            // bad password stays deliberately vague.
            $error = $result['message'];
        }
    }
}

$pageTitle = 'Admin Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Login · MarkMe</title>
<?php require __DIR__ . '/../includes/theme_boot.php'; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php require __DIR__ . '/../includes/styles.php'; ?>
</head>
<body class="admin-login-body">
<main class="admin-login-card">
    <span class="brand-mark">MarkMe <small style="font-family:var(--font-body);font-size:.62rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--accent-700)">Admin</small></span>

    <h1 style="font-size:1.4rem;margin-bottom:var(--s-2)">Sign in</h1>
    <p class="text-muted" style="font-size:.92rem;margin-bottom:var(--s-6)">
        Administrator and staff access. Customers sign in from the storefront.
    </p>

    <?php if ($error): ?>
        <div class="alert alert-danger d-flex gap-2 align-items-start" role="alert">
            <?= icon('alert', 18) ?><span><?= e($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="post">
        <?= csrf_field() ?>
        <div class="field-group">
            <label class="form-label" for="adminIdentifier">Username or email</label>
            <input type="text" id="adminIdentifier" name="identifier" class="form-control"
                   autocomplete="username" required autofocus>
        </div>
        <div class="field-group">
            <label class="form-label" for="adminPassword">Password</label>
            <input type="password" id="adminPassword" name="password" class="form-control"
                   autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block">
            <?= icon('logout', 17) ?> Log in
        </button>
    </form>

    <p style="text-align:center;margin-top:var(--s-6);font-size:.88rem">
        <a href="<?= BASE_URL ?>/customer/index.php">&larr; Back to the storefront</a>
    </p>
</main>
</body>
</html>
