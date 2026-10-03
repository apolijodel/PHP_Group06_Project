<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/customer/index.php');
}

require_guest();
csrf_check();

$returnTo = safe_local_path($_POST['return_to'] ?? null);

// Admins can close signups from Settings. Enforced server-side, so hiding the
// form in the UI is a convenience rather than the actual control.
if (setting('allow_registration', '1') !== '1') {
    flash_set('error', 'New account registration is currently closed. Please check back soon.');
    redirect($returnTo);
}

$old = [
    'username' => trim($_POST['username'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'full_name' => trim($_POST['full_name'] ?? ''),
    'contact_number' => trim($_POST['contact_number'] ?? ''),
    'address' => trim($_POST['address'] ?? ''),
];
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

$errors = [];

if ($old['username'] === '' || !preg_match('/^[A-Za-z0-9_]{3,50}$/', $old['username'])) {
    $errors[] = 'Username must be 3-50 characters (letters, numbers, underscore only).';
}
if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please provide a valid email address.';
}
if ($old['full_name'] === '') {
    $errors[] = 'Full name is required.';
}
foreach (password_policy_errors($password) as $missing) {
    $errors[] = 'Password needs: ' . $missing . '.';
}
if ($password !== $confirm) {
    $errors[] = 'Password confirmation does not match.';
}

// CAPTCHA. reCAPTCHA when keys are configured, otherwise the self-hosted
// image challenge - both verified server-side, so the claim is true either
// way. captcha_check() picks whichever is in force.
if (captcha_active() && !captcha_check($_POST)) {
    $errors[] = captcha_mode() === 'local'
        ? 'The characters you typed did not match the image. Please try again.'
        : 'Please complete the "I am not a robot" check.';
}

if (!$errors) {
    $stmt = db()->prepare('SELECT user_id FROM users WHERE username = :u OR email = :e');
    $stmt->execute(['u' => $old['username'], 'e' => $old['email']]);
    if ($stmt->fetch()) {
        $errors[] = 'Username or email is already registered.';
    }
}

if ($errors) {
    flash_set('register_errors', $errors);
    flash_set('register_old', $old);
    flash_set('reopen_modal', 'register');
    flash_set('register_return_to', $returnTo);
    redirect($returnTo);
}

// New accounts start inactive. attempt_login() refuses them until the
// activation token has been used, so this is the real gate rather than a flag
// nothing reads.
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$stmt = db()->prepare(
    'INSERT INTO users (role, username, email, password_hash, full_name, contact_number, address,
                        is_active, password_changed_at)
     VALUES (\'customer\', :username, :email, :hash, :full_name, :contact_number, :address, 0, NOW())'
);
$stmt->execute([
    'username' => $old['username'],
    'email' => $old['email'],
    'hash' => $passwordHash,
    'full_name' => $old['full_name'],
    'contact_number' => $old['contact_number'] ?: null,
    'address' => $old['address'] ?: null,
]);

$userId = (int)db()->lastInsertId();
db()->prepare('INSERT INTO carts (user_id) VALUES (:uid)')->execute(['uid' => $userId]);

password_record_change($userId, $passwordHash);

// Single-use, 24-hour activation token. Only its SHA-256 is stored, so the
// raw value below exists in the link and nowhere else.
$rawToken = activation_token_issue($userId, 24);
$activationLink = BASE_URL . '/auth/activate.php?token=' . $rawToken;

$mail = mail_send(
    $old['email'],
    'Activate your MarkMe account',
    "Hi " . $old['full_name'] . ",\n\n"
    . "Welcome to MarkMe. Confirm this address to activate your account:\n\n"
    . $activationLink . "\n\n"
    . "The link works once and expires in 24 hours.\n\n"
    . "If you did not create this account you can ignore this message.\n"
);

// No session exists yet, so the new account is named as its own actor.
log_activity(
    'account.registered',
    'Created a customer account (awaiting email activation)',
    'user',
    $userId,
    ['user_id' => $userId, 'role' => 'customer', 'username' => $old['username']],
    'Account'
);

if ($mail['sent']) {
    flash_set('login_success',
        'Account created. Check ' . $old['email'] . ' for the activation link, then log in.');
} else {
    // SMTP is not configured in this environment, so say so plainly rather
    // than claiming an email went out. The token is real either way; this
    // just hands over the link that would have been emailed.
    flash_set('activation_link', $activationLink);
    flash_set('login_success',
        'Account created, but email is not configured on this server, so no message was sent. '
        . 'Use the activation link shown below.');
}

flash_set('login_old_identifier', $old['username']);
flash_set('reopen_modal', 'login');
flash_set('login_return_to', $returnTo);
redirect($returnTo);
