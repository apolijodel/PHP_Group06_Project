<?php
/**
 * Security dashboard — administrator only.
 *
 * Overview, locked accounts, and the security settings that back them. Every
 * number on this page is read from the tables that actually enforce the
 * behaviour, so nothing here can claim a protection the code is not applying.
 *
 * Auth and the POST handlers run before admin_header.php emits any HTML,
 * otherwise redirect() would fire after headers were sent.
 */
require_once __DIR__ . '/../includes/auth.php';
require_can('security.manage');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    // ---- unlock an account ----------------------------------------------
    if ($action === 'unlock') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $username = unlock_account($userId);

        if ($username === null) {
            flash_set('error', 'That account is not locked.');
        } else {
            log_activity(
                'account.unlocked',
                'Unlocked account ' . $username,
                'user',
                $userId,
                null,
                'Security'
            );
            flash_set('success', $username . ' can sign in again. Failed attempts reset to zero.');
        }
        redirect('/admin/security.php');
    }

    // ---- security settings ----------------------------------------------
    if ($action === 'settings') {
        // Clamped server-side. A max-attempts of 0 would lock everyone out on
        // their first typo; an hour-long idle window is not a timeout.
        $attempts = max(1, min(10, (int)($_POST['max_login_attempts'] ?? 3)));
        $timeout = max(30, min(3600, (int)($_POST['session_timeout_secs'] ?? 120)));
        $minLen = max(8, min(64, (int)($_POST['password_min_length'] ?? 12)));
        $expiry = max(0, min(365, (int)($_POST['password_expiry_days'] ?? 90)));
        $lockMins = max(0, min(1440, (int)($_POST['lockout_minutes'] ?? 15)));

        $before = [
            'max_login_attempts' => sec_max_attempts(),
            'session_timeout_secs' => sec_session_timeout(),
            'password_min_length' => sec_password_min(),
            'password_expiry_days' => sec_password_expiry_days(),
            'lockout_minutes' => sec_lockout_minutes(),
        ];

        setting_put('max_login_attempts', (string)$attempts);
        setting_put('session_timeout_secs', (string)$timeout);
        setting_put('password_min_length', (string)$minLen);
        setting_put('password_expiry_days', (string)$expiry);
        setting_put('lockout_minutes', (string)$lockMins);

        $after = [
            'max_login_attempts' => $attempts,
            'session_timeout_secs' => $timeout,
            'password_min_length' => $minLen,
            'password_expiry_days' => $expiry,
            'lockout_minutes' => $lockMins,
        ];
        $changed = [];
        foreach ($after as $key => $value) {
            if ($before[$key] !== $value) {
                $changed[] = $key . ' ' . $before[$key] . ' to ' . $value;
            }
        }

        log_activity(
            'security.settings',
            $changed ? 'Changed security settings: ' . implode(', ', $changed) : 'Saved security settings',
            null,
            null,
            null,
            'Security'
        );

        flash_set('success', 'Security settings saved.');
        redirect('/admin/security.php');
    }
}

$requireCapability = 'security.manage';
$pageTitle = 'Security';
require __DIR__ . '/includes/admin_header.php';

// ---- locked accounts ----------------------------------------------------
$locked = db()->query(
    'SELECT l.*, u.full_name, u.email, u.role
       FROM account_locks l
       JOIN users u ON u.user_id = l.user_id
      WHERE l.locked_at IS NOT NULL
      ORDER BY l.locked_at DESC'
)->fetchAll();

// Accounts that have failed but are not locked yet — the early warning.
$failing = db()->query(
    'SELECT l.*, u.role
       FROM account_locks l
       JOIN users u ON u.user_id = l.user_id
      WHERE l.locked_at IS NULL AND l.failed_attempts > 0
      ORDER BY l.last_attempt_at DESC
      LIMIT 10'
)->fetchAll();

// ---- recent failed sign-ins --------------------------------------------
$recentFailures = db()->query(
    "SELECT * FROM activity_log
      WHERE action IN ('auth.failed', 'auth.mfa_failed', 'auth.blocked')
      ORDER BY created_at DESC, log_id DESC
      LIMIT 8"
)->fetchAll();

$failures24h = (int)db()->query(
    "SELECT COUNT(*) FROM activity_log
      WHERE action IN ('auth.failed', 'auth.mfa_failed')
        AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)"
)->fetchColumn();

// ---- MFA adoption -------------------------------------------------------
$mfaCounts = db()->query(
    'SELECT COUNT(*) AS total, COALESCE(SUM(mfa_enabled), 0) AS enabled FROM users'
)->fetch();

$pendingActivation = (int)db()->query(
    'SELECT COUNT(*) FROM users WHERE is_active = 0'
)->fetchColumn();
?>

<div class="page-head">
    <div>
        <p class="eyebrow eyebrow-muted">Security</p>
        <h2 style="margin-top:var(--s-2)">Security overview</h2>
        <p>Account protection, locked accounts and the settings behind them.
            Every figure here is read from the tables that enforce the behaviour.</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/audit_logs.php" class="btn btn-secondary">
        <?= icon('list', 16) ?> Audit logs
    </a>
</div>

<!-- ------------------------------ overview ------------------------------- -->
<div class="stat-row" data-reveal-stagger style="margin-bottom:var(--s-6)">
    <div class="stat-card <?= $locked ? 'is-accent' : '' ?>">
        <div class="stat-top">
            <span class="stat-label">Locked accounts</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('shield', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= count($locked) ?></span>
        <span class="stat-note"><?= $locked ? 'Need an administrator to unlock' : 'Nothing locked out' ?></span>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Failed sign-ins (24h)</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('alert', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= $failures24h ?></span>
        <span class="stat-note">Wrong password or wrong code</span>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Two-factor enabled</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('key', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= (int)$mfaCounts['enabled'] ?> / <?= (int)$mfaCounts['total'] ?></span>
        <span class="stat-note">Accounts with an authenticator app</span>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Awaiting activation</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('mail', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= $pendingActivation ?></span>
        <span class="stat-note">Registered but not yet activated</span>
    </div>
</div>

<div class="cart-grid">
    <div class="d-flex flex-column" style="gap:var(--s-6)">
        <!-- ------------------------ locked accounts ------------------------ -->
        <section class="card card-flush">
            <div class="panel-head">
                <h2>Locked accounts</h2>
                <span class="text-muted" style="font-size:.85rem">
                    Locked after <?= sec_max_attempts() ?> failed attempts
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <caption class="visually-hidden">Accounts locked out of signing in</caption>
                    <thead>
                        <tr>
                            <th scope="col">Username</th>
                            <th scope="col">User</th>
                            <th scope="col">Failed attempts</th>
                            <th scope="col">Locked at</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="visually-hidden">Action</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($locked as $row): ?>
                            <tr>
                                <th scope="row" style="font-weight:600">@<?= e($row['username']) ?></th>
                                <td style="font-size:.85rem">
                                    <span style="display:block"><?= e($row['full_name']) ?></span>
                                    <span class="text-muted"><?= e(role_label($row['role'])) ?></span>
                                </td>
                                <td class="mono-num"><?= (int)$row['failed_attempts'] ?></td>
                                <td class="text-muted" style="font-size:.85rem;white-space:nowrap">
                                    <?= e(date('M j, Y g:i A', strtotime((string)$row['locked_at']))) ?>
                                </td>
                                <td><span class="badge bg-danger">Locked</span></td>
                                <td>
                                    <form method="post"
                                          data-confirm
                                          data-confirm-title="Unlock this account?"
                                          data-confirm-body="<?= e($row['username']) ?> will be able to sign in again."
                                          data-confirm-note="Their failed attempt count resets to zero."
                                          data-confirm-action="Unlock"
                                          data-confirm-tone="default">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="unlock">
                                        <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>">
                                        <button type="submit" class="btn btn-outline-success btn-sm">
                                            <?= icon('check', 14) ?> Unlock account
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$locked): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <span class="empty-icon"><?= icon('check-circle', 24) ?></span>
                                        <h3>No locked accounts</h3>
                                        <p>Accounts appear here after <?= sec_max_attempts() ?> failed
                                            sign-in attempts, and only an administrator can release them.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php if ($failing): ?>
            <!-- Not locked yet, but on the way there. -->
            <section class="card card-flush">
                <div class="panel-head"><h2>Accounts with failed attempts</h2></div>
                <div class="table-responsive">
                    <table class="table">
                        <caption class="visually-hidden">Accounts that have failed a sign-in but are not locked</caption>
                        <thead>
                            <tr>
                                <th scope="col">Username</th>
                                <th scope="col">Attempts</th>
                                <th scope="col">Last attempt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($failing as $row): ?>
                                <tr>
                                    <th scope="row" style="font-weight:500">@<?= e($row['username']) ?></th>
                                    <td class="mono-num">
                                        <?= (int)$row['failed_attempts'] ?> of <?= sec_max_attempts() ?>
                                    </td>
                                    <td class="text-muted" style="font-size:.85rem">
                                        <?= e(time_ago((string)$row['last_attempt_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>

        <!-- ----------------------- recent security events ------------------ -->
        <section class="card card-flush">
            <div class="panel-head">
                <h2>Recent failed sign-ins</h2>
                <a href="<?= BASE_URL ?>/admin/audit_logs.php?module=Authentication"
                   class="btn btn-secondary btn-sm">All authentication events <?= icon('chevron-right', 14) ?></a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <caption class="visually-hidden">The most recent failed sign-in attempts</caption>
                    <thead>
                        <tr>
                            <th scope="col">When</th>
                            <th scope="col">Account</th>
                            <th scope="col">What happened</th>
                            <th scope="col">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentFailures as $row): ?>
                            <tr>
                                <td class="text-muted" style="font-size:.85rem;white-space:nowrap">
                                    <?= e(time_ago((string)$row['created_at'])) ?>
                                </td>
                                <th scope="row" style="font-weight:500"><?= e($row['actor_name']) ?></th>
                                <td style="font-size:.85rem"><?= e($row['summary']) ?></td>
                                <td class="mono-num" style="font-size:.8rem"><?= e($row['ip_address'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$recentFailures): ?>
                            <tr><td colspan="4" class="text-muted">No failed sign-ins recorded.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- --------------------------- configuration --------------------------- -->
    <aside class="security-aside">
        <?php /* A plain card, not .summary-card: that one is position:sticky for
                 the checkout sidebar, and here it detached and overlapped the
                 Integration status card below it. Two ordinary cards in a
                 column is the right structure, not a bigger margin. */ ?>
        <section class="card card-flush">
            <div class="panel-head">
                <h2 class="d-flex align-items-center gap-2"><?= icon('settings', 18) ?> Security settings</h2>
            </div>
            <div class="panel-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="settings">

                    <div class="field-group">
                        <label class="form-label" for="secAttempts">Maximum failed login attempts</label>
                        <input type="number" id="secAttempts" name="max_login_attempts" class="form-control"
                               min="1" max="10" value="<?= sec_max_attempts() ?>" required>
                        <p class="form-text"><strong>Required default: 3.</strong> Raising it weakens the
                            lockout the coursework asks for.</p>
                    </div>

                    <div class="field-group">
                        <label class="form-label" for="secTimeout">Session timeout (seconds)</label>
                        <input type="number" id="secTimeout" name="session_timeout_secs" class="form-control"
                               min="30" max="3600" value="<?= sec_session_timeout() ?>" required>
                        <p class="form-text">Demonstration baseline: 120 (2 minutes). Idle sessions are
                            destroyed and the user is told why.</p>
                    </div>

                    <div class="field-group">
                        <label class="form-label" for="secMinLen">Minimum password length</label>
                        <input type="number" id="secMinLen" name="password_min_length" class="form-control"
                               min="8" max="64" value="<?= sec_password_min() ?>" required>
                        <p class="form-text">Required default: 12, plus upper, lower, number and symbol.</p>
                    </div>

                    <div class="field-group">
                        <label class="form-label" for="secExpiry">Password expires after (days)</label>
                        <input type="number" id="secExpiry" name="password_expiry_days" class="form-control"
                               min="0" max="365" value="<?= sec_password_expiry_days() ?>" required>
                        <p class="form-text">0 turns expiry off.</p>
                    </div>

                    <div class="field-group">
                        <label class="form-label" for="secLockout">Lockout lasts (minutes)</label>
                        <input type="number" id="secLockout" name="lockout_minutes" class="form-control"
                               min="0" max="1440" value="<?= sec_lockout_minutes() ?>" required>
                        <p class="form-text">
                            After this long a lock lifts itself and the counter resets.
                            <strong>0 means only an administrator can unlock.</strong>
                            Setting 0 is riskier than it sounds: if the only administrator
                            locks themselves out, nobody is left who can let them back in.
                            Recover with <code>php database/unlock_account.php &lt;username&gt;</code>.
                        </p>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <?= icon('save', 16) ?> Save security settings
                    </button>
                </form>
            </div>
        </section>

        <!-- Honest status for the two integrations that need outside keys. -->
        <section class="card card-flush">
            <div class="panel-head">
                <h2 class="d-flex align-items-center gap-2"><?= icon('info', 18) ?> Integration status</h2>
            </div>
            <div class="panel-body">
                <ul class="perm-list <?= captcha_active() ? 'is-allowed' : 'is-denied' ?>"
                    style="margin-bottom:var(--s-3)">
                    <li>
                        <?= icon(captcha_active() ? 'check' : 'close', 15) ?>
                        <span>
                            <strong>CAPTCHA</strong> —
                            <?php if (captcha_mode() === 'recaptcha'): ?>
                                Google reCAPTCHA is configured and verified on registration.
                            <?php elseif (captcha_mode() === 'local'): ?>
                                Built-in image CAPTCHA is active and verified server-side on
                                registration. Google reCAPTCHA is not configured; add
                                RECAPTCHA_SITE_KEY and RECAPTCHA_SECRET_KEY to config.php to
                                switch to it.
                            <?php else: ?>
                                NOT ACTIVE. Neither reCAPTCHA keys nor PHP's GD extension are
                                available, so registration is not CAPTCHA protected and does
                                not claim to be.
                            <?php endif; ?>
                        </span>
                    </li>
                </ul>
                <ul class="perm-list <?= mail_configured() ? 'is-allowed' : 'is-denied' ?>" style="margin:0">
                    <li>
                        <?= icon(mail_configured() ? 'check' : 'close', 15) ?>
                        <span>
                            <strong>Activation email (SMTP)</strong> —
                            <?= mail_configured()
                                ? 'configured. Activation links are emailed on registration.'
                                : 'NOT CONFIGURED. Accounts are still created inactive with a real single-use token; the message is written to storage/outbox instead of being sent.' ?>
                        </span>
                    </li>
                </ul>
            </div>
        </section>
    </aside>
</div>

<?php require __DIR__ . '/../includes/backoffice/shell_footer.php'; ?>
