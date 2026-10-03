<?php
/**
 * MFA enrolment handler, shared by the customer Settings page and the admin
 * Profile page so there is one implementation of enabling two-factor auth.
 *
 * Three actions, all POST and all CSRF-checked:
 *   start    generate a candidate secret and show the QR
 *   confirm  verify a code against that candidate before enabling anything
 *   disable  turn it off, requiring the current password
 *
 * The candidate secret is held in the session, not written to users, until a
 * code proves the authenticator app actually has it. Enabling first and
 * verifying later is how people lock themselves out.
 */
require_once __DIR__ . '/../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/customer/settings.php');
}
csrf_check();

$user = current_user();
$userId = (int)$user['user_id'];
$isManager = is_manager();
$back = $isManager ? '/admin/profile.php' : '/customer/settings.php';
$action = $_POST['action'] ?? '';

/* ---------------------------------------------------------------- start */
if ($action === 'start') {
    $_SESSION['mfa_candidate'] = totp_secret();
    redirect($back . '#mfa');
}

/* -------------------------------------------------------------- confirm */
if ($action === 'confirm') {
    $candidate = $_SESSION['mfa_candidate'] ?? '';
    $code = trim($_POST['code'] ?? '');

    if ($candidate === '') {
        flash_set('error', 'Start the setup again — that enrolment expired.');
        redirect($back . '#mfa');
    }

    if (!totp_verify($candidate, $code)) {
        // Deliberately does not enable anything: an unverified secret would
        // mean the next sign-in asks for codes nobody can produce.
        flash_set('error', 'That code did not match. Check the app and try again.');
        redirect($back . '#mfa');
    }

    db()->prepare(
        'UPDATE users SET mfa_secret = :secret, mfa_enabled = 1, mfa_confirmed_at = NOW()
          WHERE user_id = :id'
    )->execute(['secret' => $candidate, 'id' => $userId]);

    unset($_SESSION['mfa_candidate']);

    // Keep the live session in step, so the Settings page reflects reality
    // without a re-login.
    $_SESSION['user']['mfa_enabled'] = 1;

    log_activity(
        'account.mfa_enabled',
        'Enabled two-factor authentication',
        'user',
        $userId,
        $user,
        'Security'
    );

    flash_set('success', 'Two-factor authentication is on. You will be asked for a code at your next sign-in.');
    redirect($back . '#mfa');
}

/* -------------------------------------------------------------- disable */
if ($action === 'disable') {
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT password_hash FROM users WHERE user_id = :id');
    $stmt->execute(['id' => $userId]);
    $hash = (string)$stmt->fetchColumn();

    // Turning a protection off is a sensitive action: prove it is really them
    // at the keyboard, not someone using a borrowed session.
    if (!password_verify($password, $hash)) {
        flash_set('error', 'That password is not correct, so two-factor authentication was left on.');
        redirect($back . '#mfa');
    }

    db()->prepare(
        'UPDATE users SET mfa_secret = NULL, mfa_enabled = 0, mfa_confirmed_at = NULL
          WHERE user_id = :id'
    )->execute(['id' => $userId]);

    $_SESSION['user']['mfa_enabled'] = 0;

    log_activity(
        'account.mfa_disabled',
        'Disabled two-factor authentication',
        'user',
        $userId,
        $user,
        'Security'
    );

    flash_set('success', 'Two-factor authentication is off.');
    redirect($back . '#mfa');
}

redirect($back);
