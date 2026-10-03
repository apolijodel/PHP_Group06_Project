<?php
/**
 * Account activation.
 *
 * The token in the link is compared by SHA-256 against
 * email_verification_tokens. Expiry and single use are enforced inside
 * activation_token_consume(), in SQL, not by trusting the caller — so a
 * replayed or stale link fails even if it reaches this page.
 */
require_once __DIR__ . '/../includes/auth.php';

$result = activation_token_consume((string)($_GET['token'] ?? ''));

if ($result['ok']) {
    $stmt = db()->prepare('SELECT username, full_name FROM users WHERE user_id = :id');
    $stmt->execute(['id' => $result['user_id']]);
    $account = $stmt->fetch() ?: ['username' => '', 'full_name' => ''];

    log_activity(
        'account.activated',
        'Account activated by email link',
        'user',
        $result['user_id'],
        ['user_id' => $result['user_id'], 'role' => 'customer', 'username' => $account['username']],
        'Account'
    );
}

$pageTitle = $result['ok'] ? 'Account activated' : 'Activation problem';
require __DIR__ . '/../includes/customer/header.php';
?>

<div class="account-page">
    <div class="card empty-state" style="padding:var(--s-10) var(--s-6)">
        <?php if ($result['ok']): ?>
            <span class="empty-icon"><?= icon('check-circle', 28) ?></span>
            <h1 style="font-size:1.5rem">You're all set</h1>
            <p>Your account is active. Sign in and start designing.</p>
            <a href="<?= BASE_URL ?>/customer/index.php" class="btn btn-primary"
               style="margin-top:var(--s-5)" data-login-open>
                Go to MarkMe
            </a>
        <?php else: ?>
            <span class="empty-icon"><?= icon('alert', 28) ?></span>
            <h1 style="font-size:1.5rem">That link didn't work</h1>
            <p><?= e($result['reason']) ?></p>
            <p class="form-text">
                Activation links can be used once and expire after 24 hours.
                Register again, or ask an administrator to activate the account.
            </p>
            <a href="<?= BASE_URL ?>/customer/index.php" class="btn btn-secondary"
               style="margin-top:var(--s-5)">Back to MarkMe</a>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../includes/customer/footer.php'; ?>
