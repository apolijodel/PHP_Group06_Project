<?php
/**
 * Screen: profile - server-side half.
 *
 * Auth, form handling and data loading, shared by the administrator
 * panel and the staff panel. Runs before either shell opens any
 * output, so redirect() still works.
 */
/**
 * Admin profile — this administrator's own account, separate from Settings.
 *
 * Settings owns the store; this page owns the person signed in. Administrators
 * previously had to edit their details on the customer storefront, which is why
 * this page exists.
 */
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../upload.php';
require_manager();

$userId = (int)current_user()['user_id'];
$stmt = db()->prepare('SELECT * FROM users WHERE user_id = :id');
$stmt->execute(['id' => $userId]);
$admin = $stmt->fetch();

$errors = [];
$pwErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    csrf_check();
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');

    if ($full_name === '') {
        $errors[] = 'Full name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        $errors[] = 'Invalid username format.';
    }

    if (!$errors) {
        $stmt = db()->prepare('SELECT user_id FROM users WHERE (username = :u OR email = :e) AND user_id != :id');
        $stmt->execute(['u' => $username, 'e' => $email, 'id' => $userId]);
        if ($stmt->fetch()) {
            $errors[] = 'That username or email is already taken.';
        }
    }

    $newAvatar = null;
    if (!$errors && !empty($_FILES['avatar']['name'])) {
        try {
            $newAvatar = handle_image_upload($_FILES['avatar'], __DIR__ . '/../../../uploads/avatars');
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (!$errors) {
        $sql = 'UPDATE users SET full_name = :full_name, email = :email, username = :username,
                contact_number = :contact_number';
        $params = [
            'full_name' => $full_name,
            'email' => $email,
            'username' => $username,
            'contact_number' => $contact_number ?: null,
            'id' => $userId,
        ];
        if ($newAvatar !== null) {
            $sql .= ', avatar_path = :avatar_path';
            $params['avatar_path'] = $newAvatar;
        }
        $sql .= ' WHERE user_id = :id';

        db()->prepare($sql)->execute($params);

        if ($newAvatar !== null && !empty($admin['avatar_path'])) {
            $previous = __DIR__ . '/../../../uploads/avatars/' . basename($admin['avatar_path']);
            if (is_file($previous)) {
                @unlink($previous);
            }
        }

        $_SESSION['user']['full_name'] = $full_name;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['username'] = $username;
        if ($newAvatar !== null) {
            $_SESSION['user']['avatar_path'] = $newAvatar;
        }

        flash_set('success', 'Profile updated.');
        redirect('/admin/profile.php');
    }

    $admin = array_merge($admin, compact('full_name', 'email', 'username', 'contact_number'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    csrf_check();
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $admin['password_hash'])) {
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
    if (!$pwErrors && password_was_used_before($userId, $new)) {
        $pwErrors[] = 'You have used that password recently. Please choose a different one.';
    }

    if (!$pwErrors) {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        db()->prepare('UPDATE users SET password_hash = :hash WHERE user_id = :id')
            ->execute(['hash' => $hash, 'id' => $userId]);

        password_record_change($userId, $hash);
        unset($_SESSION['password_expired']);
        session_regenerate_id(true);

        log_activity('account.password', 'Changed their password', 'user', $userId, null, 'Security');

        flash_set('success', 'Password changed successfully.');
        redirect('/admin/profile.php');
    }
}

$avatar = avatar_url($admin['avatar_path']);

$requireCapability = 'profile.self';
$pageTitle = 'Profile';
