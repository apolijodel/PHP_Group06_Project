<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/customer/index.php');
}

require_guest();
csrf_check();

$identifier = trim($_POST['identifier'] ?? '');
$password = $_POST['password'] ?? '';
$returnTo = safe_local_path($_POST['return_to'] ?? null);

if ($identifier === '' || $password === '') {
    flash_set('login_error', 'Please enter your username and password.');
    flash_set('login_old_identifier', $identifier);
    flash_set('reopen_modal', 'login');
    flash_set('login_return_to', $returnTo);
    redirect($returnTo);
}

$result = attempt_login($identifier, $password);

if ($result['status'] === 'ok') {
    $user = $result['user'];
    redirect(is_manager() ? panel_base() . '/dashboard.php' : $returnTo);
}

// Password was right but the account carries a second factor. The session is
// only half open at this point - $_SESSION['user'] is not set - so this is a
// gate rather than a suggestion.
if ($result['status'] === 'mfa_required') {
    redirect('/auth/mfa.php?return=' . urlencode($returnTo));
}

flash_set('login_error', $result['message']);
flash_set('login_old_identifier', $identifier);
flash_set('reopen_modal', 'login');
flash_set('login_return_to', $returnTo);
redirect($returnTo);
