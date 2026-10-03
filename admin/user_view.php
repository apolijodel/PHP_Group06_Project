<?php
/**
 * One account's profile — administrator only.
 *
 * Reached from the audit log, where an administrator needs to see whose
 * account produced an entry regardless of role. customer_view.php stays as
 * the customer screen with orders and saved designs; this is the account
 * view that works for staff and administrators too.
 *
 * Deliberately selects named columns rather than SELECT *: password_hash and
 * mfa_secret are never read, so they cannot leak into the page by accident
 * through some later edit. MFA is reported as on or off and nothing more.
 */
require_once __DIR__ . '/../includes/auth.php';
require_can('security.manage');

$userId = (int)($_GET['id'] ?? 0);

$stmt = db()->prepare(
    'SELECT user_id, role, username, email, full_name, avatar_path, contact_number,
            address, city, province, postal_code, is_active, created_at, activated_at,
            mfa_enabled, mfa_confirmed_at, password_changed_at
       FROM users WHERE user_id = :id'
);
$stmt->execute(['id' => $userId]);
$account = $stmt->fetch();

$requireCapability = 'security.manage';
$pageTitle = $account ? $account['username'] : 'Account';
require __DIR__ . '/includes/admin_header.php';

if (!$account) {
    ?>
    <div class="card empty-state">
        <span class="empty-icon"><?= icon('user', 26) ?></span>
        <h2>Account not found</h2>
        <p>It may have been deleted since the audit entry was recorded.</p>
        <a href="<?= BASE_URL ?>/admin/audit_logs.php" class="btn btn-primary" style="margin-top:var(--s-4)">
            Back to audit logs
        </a>
    </div>
    <?php
    require __DIR__ . '/../includes/backoffice/shell_footer.php';
    exit;
}

// Lock state, so an administrator can act on what they are looking at.
$lock = lock_row($userId);

// This account's own recent history.
$eventStmt = db()->prepare(
    'SELECT * FROM activity_log WHERE user_id = :id
      ORDER BY created_at DESC, log_id DESC LIMIT 10'
);
$eventStmt->execute(['id' => $userId]);
$events = $eventStmt->fetchAll();

$avatar = avatar_url($account['avatar_path']);
?>

<nav class="breadcrumbs" aria-label="Breadcrumb">
    <a href="<?= BASE_URL ?>/admin/audit_logs.php">Audit logs</a>
    <span class="sep" aria-hidden="true"><?= icon('chevron-right', 13) ?></span>
    <span aria-current="page"><?= e($account['username']) ?></span>
</nav>

<div class="page-head">
    <div>
        <p class="eyebrow eyebrow-muted">Security</p>
        <h2 style="margin-top:var(--s-2)"><?= e($account['full_name']) ?></h2>
        <p>@<?= e($account['username']) ?> &middot; <?= e(role_label($account['role'])) ?></p>
    </div>
    <?php if ($account['role'] === 'customer'): ?>
        <a href="<?= BASE_URL ?>/admin/customer_view.php?id=<?= (int)$account['user_id'] ?>"
           class="btn btn-secondary">
            <?= icon('receipt', 16) ?> Orders and designs
        </a>
    <?php endif; ?>
</div>

<div class="cart-grid">
    <div class="d-flex flex-column" style="gap:var(--s-6)">
        <!-- ---------------------------- account ---------------------------- -->
        <section class="card card-flush">
            <div class="panel-head"><h2>Account</h2></div>
            <div class="panel-body">
                <div class="form-grid">
                    <div>
                        <p class="eyebrow eyebrow-muted">Email</p>
                        <p style="margin-top:var(--s-2)"><?= e($account['email']) ?></p>
                    </div>
                    <div>
                        <p class="eyebrow eyebrow-muted">Contact</p>
                        <p style="margin-top:var(--s-2)"><?= e($account['contact_number'] ?: '—') ?></p>
                    </div>
                    <div>
                        <p class="eyebrow eyebrow-muted">Role</p>
                        <p style="margin-top:var(--s-2)">
                            <span class="badge badge-role r-<?= e($account['role']) ?>">
                                <?= e(role_label($account['role'])) ?>
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="eyebrow eyebrow-muted">Status</p>
                        <p style="margin-top:var(--s-2)">
                            <span class="badge bg-<?= $account['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $account['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="eyebrow eyebrow-muted">Registered</p>
                        <p style="margin-top:var(--s-2)">
                            <?= e(date('M j, Y', strtotime((string)$account['created_at']))) ?>
                        </p>
                    </div>
                    <div>
                        <p class="eyebrow eyebrow-muted">Activated</p>
                        <p style="margin-top:var(--s-2)">
                            <?= $account['activated_at']
                                ? e(date('M j, Y', strtotime((string)$account['activated_at'])))
                                : 'Not activated' ?>
                        </p>
                    </div>
                    <?php if ($account['role'] === 'customer' && full_address($account) !== ''): ?>
                        <div class="span-2">
                            <p class="eyebrow eyebrow-muted">Delivery address</p>
                            <p style="margin-top:var(--s-2)"><?= e(full_address($account)) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- --------------------------- recent activity --------------------- -->
        <section class="card card-flush">
            <div class="panel-head">
                <h2>Recent activity</h2>
                <a href="<?= BASE_URL ?>/admin/audit_logs.php?who=user:<?= (int)$account['user_id'] ?>"
                   class="btn btn-secondary btn-sm">
                    All of their entries <?= icon('chevron-right', 14) ?>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <caption class="visually-hidden">This account's ten most recent audit entries</caption>
                    <thead>
                        <tr>
                            <th scope="col">When</th>
                            <th scope="col">Module</th>
                            <th scope="col">What happened</th>
                            <th scope="col">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $ev): ?>
                            <tr>
                                <td class="text-muted" style="font-size:.82rem;white-space:nowrap">
                                    <?= e(time_ago((string)$ev['created_at'])) ?>
                                </td>
                                <td style="font-size:.82rem"><?= e((string)($ev['module'] ?? '—')) ?></td>
                                <td style="font-size:.85rem"><?= e($ev['summary']) ?></td>
                                <td class="mono-num" style="font-size:.78rem"><?= e((string)($ev['ip_address'] ?? '—')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$events): ?>
                            <tr><td colspan="4" class="text-muted">Nothing recorded for this account yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- ----------------------------- security ------------------------------ -->
    <aside class="d-flex flex-column" style="gap:var(--s-5)">
        <section class="card card-flush">
            <div class="panel-head">
                <h2 class="d-flex align-items-center gap-2"><?= icon('shield', 18) ?> Sign-in security</h2>
            </div>
            <div class="panel-body">
                <?php if ($avatar): ?>
                    <img class="avatar-img" src="<?= e($avatar) ?>" alt=""
                         style="width:64px;height:64px;border-radius:var(--r-pill);margin-bottom:var(--s-4)" loading="lazy" decoding="async">
                <?php endif; ?>

                <div class="setting-row">
                    <div>
                        <p class="setting-title">Two-factor authentication</p>
                        <p class="form-text" style="margin:0">
                            <?= $account['mfa_enabled']
                                ? 'On since ' . e(date('M j, Y', strtotime((string)$account['mfa_confirmed_at'])))
                                : 'Not enabled on this account.' ?>
                        </p>
                    </div>
                    <span class="badge bg-<?= $account['mfa_enabled'] ? 'success' : 'secondary' ?>">
                        <?= $account['mfa_enabled'] ? 'On' : 'Off' ?>
                    </span>
                </div>

                <div class="setting-row">
                    <div>
                        <p class="setting-title">Password last changed</p>
                        <p class="form-text" style="margin:0">
                            <?= $account['password_changed_at']
                                ? e(date('M j, Y', strtotime((string)$account['password_changed_at'])))
                                : 'Never recorded' ?>
                        </p>
                    </div>
                </div>

                <div class="setting-row">
                    <div>
                        <p class="setting-title">Account lock</p>
                        <p class="form-text" style="margin:0">
                            <?php if ($lock && $lock['locked_at']): ?>
                                Locked <?= e(time_ago((string)$lock['locked_at'])) ?>
                                after <?= (int)$lock['failed_attempts'] ?> failed attempts.
                            <?php elseif ($lock): ?>
                                <?= (int)$lock['failed_attempts'] ?> failed
                                attempt<?= (int)$lock['failed_attempts'] === 1 ? '' : 's' ?>, not locked.
                            <?php else: ?>
                                No failed sign-in attempts recorded.
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if ($lock && $lock['locked_at']): ?>
                        <span class="badge bg-danger">Locked</span>
                    <?php endif; ?>
                </div>

                <?php if ($lock && $lock['locked_at']): ?>
                    <form method="post" action="<?= BASE_URL ?>/admin/security.php"
                          style="margin-top:var(--s-4)"
                          data-confirm
                          data-confirm-title="Unlock this account?"
                          data-confirm-body="<?= e($account['username']) ?> will be able to sign in again."
                          data-confirm-note="Their failed attempt count resets to zero."
                          data-confirm-action="Unlock"
                          data-confirm-tone="default">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="unlock">
                        <input type="hidden" name="user_id" value="<?= (int)$account['user_id'] ?>">
                        <button type="submit" class="btn btn-outline-success btn-block">
                            <?= icon('check', 16) ?> Unlock account
                        </button>
                    </form>
                <?php endif; ?>

                <p class="form-text" style="margin-top:var(--s-4)">
                    Passwords, authenticator secrets and activation tokens are never shown here,
                    to anyone.
                </p>
            </div>
        </section>
    </aside>
</div>

<?php require __DIR__ . '/../includes/backoffice/shell_footer.php'; ?>
