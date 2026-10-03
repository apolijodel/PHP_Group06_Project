<?php
/**
 * Administrator accounts — administrator only.
 *
 * The counterpart to staff.php, scoped to role = 'admin' in every query for
 * the same reason: the WHERE clause, not a hidden button, is what stops this
 * screen reaching accounts it has no business touching.
 *
 * Two rules exist here that staff.php does not need, both about not locking
 * everyone out of the back office:
 *   - you cannot deactivate your own account
 *   - you cannot deactivate the last active administrator
 * Both are enforced server-side, so a crafted POST cannot get round them.
 */
require_once __DIR__ . '/../includes/auth.php';
require_can('admins.manage');

$selfId = (int)current_user()['user_id'];
$errors = [];

/** Active administrators other than the one passed in. */
$otherActiveAdmins = static function (int $exceptId): int {
    $stmt = db()->prepare(
        "SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1 AND user_id != :id"
    );
    $stmt->execute(['id' => $exceptId]);
    return (int)$stmt->fetchColumn();
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    // ---- create an administrator ----------------------------------------
    if ($action === 'create') {
        $full_name = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact = trim($_POST['contact_number'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($full_name === '') {
            $errors[] = 'Full name is required.';
        }
        if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
            $errors[] = 'Username must be 3–50 characters: letters, numbers and underscores.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }
        foreach (password_policy_errors($password) as $missing) {
            $errors[] = 'Password needs: ' . $missing . '.';
        }

        if (!$errors) {
            $stmt = db()->prepare('SELECT user_id FROM users WHERE username = :u OR email = :e');
            $stmt->execute(['u' => $username, 'e' => $email]);
            if ($stmt->fetch()) {
                $errors[] = 'That username or email is already taken.';
            }
        }

        if (!$errors) {
            db()->prepare(
                "INSERT INTO users (role, username, email, password_hash, full_name, contact_number, is_active)
                 VALUES ('admin', :username, :email, :hash, :full_name, :contact, 1)"
            )->execute([
                'username' => $username,
                'email' => $email,
                'hash' => password_hash($password, PASSWORD_DEFAULT),
                'full_name' => $full_name,
                'contact' => $contact ?: null,
            ]);

            log_activity(
                'admin.created',
                'Created administrator account ' . $username,
                'user',
                (int)db()->lastInsertId()
            );
            flash_set('success', 'Administrator account created for ' . $username . '.');
            redirect('/admin/admins.php');
        }
    }

    // ---- activate / deactivate -------------------------------------------
    if ($action === 'toggle') {
        $adminId = (int)($_POST['user_id'] ?? 0);

        $stmt = db()->prepare("SELECT username, is_active FROM users WHERE user_id = :id AND role = 'admin'");
        $stmt->execute(['id' => $adminId]);
        $row = $stmt->fetch();

        if (!$row) {
            flash_set('error', 'Administrator account not found.');
        } elseif ($adminId === $selfId) {
            flash_set('error', 'You cannot deactivate your own account.');
        } elseif ($row['is_active'] && $otherActiveAdmins($adminId) === 0) {
            flash_set('error', 'This is the last active administrator. Deactivating it would lock everyone out.');
        } else {
            $newStatus = $row['is_active'] ? 0 : 1;
            db()->prepare("UPDATE users SET is_active = :s WHERE user_id = :id AND role = 'admin'")
                ->execute(['s' => $newStatus, 'id' => $adminId]);

            log_activity(
                $newStatus ? 'admin.enabled' : 'admin.disabled',
                ($newStatus ? 'Reactivated' : 'Deactivated') . ' administrator ' . $row['username'],
                'user',
                $adminId
            );
            flash_set('success', $newStatus
                ? 'Administrator account reactivated.'
                : 'Administrator account deactivated. They can no longer sign in.');
        }
        redirect('/admin/admins.php');
    }

    // ---- reset a password -------------------------------------------------
    if ($action === 'reset_password') {
        $adminId = (int)($_POST['user_id'] ?? 0);
        $password = $_POST['new_password'] ?? '';

        if ($failed = password_policy_errors($password)) {
            flash_set('error', password_policy_message($failed));
            redirect('/admin/admins.php');
        }

        $stmt = db()->prepare("UPDATE users SET password_hash = :h WHERE user_id = :id AND role = 'admin'");
        $stmt->execute(['h' => password_hash($password, PASSWORD_DEFAULT), 'id' => $adminId]);

        if ($stmt->rowCount()) {
            log_activity('admin.password', 'Reset an administrator password', 'user', $adminId);
            flash_set('success', 'Password reset.');
        } else {
            flash_set('error', 'Administrator account not found.');
        }
        redirect('/admin/admins.php');
    }
}

$admins = db()->query(
    "SELECT user_id, username, email, full_name, avatar_path, contact_number, is_active, created_at
       FROM users WHERE role = 'admin' ORDER BY created_at ASC"
)->fetchAll();

$activeCount = count(array_filter($admins, static fn($a) => (int)$a['is_active'] === 1));

$requireCapability = 'admins.manage';
$pageTitle = 'Admins';
require __DIR__ . '/includes/admin_header.php';
?>

<div class="page-head">
    <div>
        <p class="eyebrow eyebrow-muted">People</p>
        <h2 style="margin-top:var(--s-2)">Administrator accounts</h2>
        <p><?= count($admins) ?> <?= count($admins) === 1 ? 'account' : 'accounts' ?>,
            <?= $activeCount ?> active. Administrators have unrestricted access to the back office.</p>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger d-flex gap-2 align-items-start" role="alert"
         style="margin-bottom:var(--s-5)">
        <?= icon('alert', 20) ?>
        <ul style="margin:0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="cart-grid">
    <div class="d-flex flex-column" style="gap:var(--s-6)">
        <div class="data-card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <caption class="visually-hidden">Administrator accounts</caption>
                    <thead>
                        <tr>
                            <th scope="col">Administrator</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Added</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $member): ?>
                            <?php
                            $avatar = avatar_url($member['avatar_path']);
                            $isSelf = (int)$member['user_id'] === $selfId;
                            // Mirrors the server-side rule, so the button is
                            // absent for exactly the cases the POST refuses.
                            $isLastActive = (int)$member['is_active'] === 1
                                && $otherActiveAdmins((int)$member['user_id']) === 0;
                            ?>
                            <tr>
                                <th scope="row">
                                    <span class="d-flex align-items-center gap-2">
                                        <?php if ($avatar): ?>
                                            <img class="avatar avatar-img" src="<?= e($avatar) ?>" alt=""
                                                 width="32" height="32"
                                                 style="width:32px;height:32px;border-radius:var(--r-pill)" loading="lazy" decoding="async">
                                        <?php else: ?>
                                            <span class="avatar" aria-hidden="true"
                                                  style="width:32px;height:32px;border-radius:var(--r-pill);background:var(--accent-600);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;flex:none">
                                                <?= e(initials($member['full_name'])) ?>
                                            </span>
                                        <?php endif; ?>
                                        <span>
                                            <span style="display:block;font-weight:600">
                                                <?= e($member['full_name']) ?>
                                                <?php if ($isSelf): ?>
                                                    <span class="badge badge-cat" style="margin-left:.25rem">You</span>
                                                <?php endif; ?>
                                            </span>
                                            <span class="text-muted" style="font-size:.8rem;font-weight:400">@<?= e($member['username']) ?></span>
                                        </span>
                                    </span>
                                </th>
                                <td style="font-size:.85rem">
                                    <span style="display:block"><?= e($member['email']) ?></span>
                                    <span class="text-muted"><?= e($member['contact_number'] ?: '—') ?></span>
                                </td>
                                <td class="text-muted" style="font-size:.85rem;white-space:nowrap">
                                    <?= e(date('M j, Y', strtotime($member['created_at']))) ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $member['is_active'] ? 'success' : 'secondary' ?>">
                                        <?= $member['is_active'] ? 'Active' : 'Deactivated' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="staff-actions">
                                        <?php if ($isSelf): ?>
                                            <!-- Kept short: this cell sits in a table that would
                                                 otherwise need horizontal scrolling to read. -->
                                            <a href="<?= BASE_URL ?>/admin/profile.php"
                                               class="btn btn-secondary btn-sm">
                                                <?= icon('user', 14) ?> Your profile
                                            </a>
                                        <?php elseif ($isLastActive): ?>
                                            <span class="badge badge-cat">Last active admin</span>
                                        <?php else: ?>
                                            <form method="post"
                                                  data-confirm
                                                  data-confirm-title="<?= $member['is_active'] ? 'Deactivate' : 'Reactivate' ?> this account?"
                                                  data-confirm-body="<?= e($member['username']) ?><?= $member['is_active'] ? ' will no longer be able to sign in.' : ' will be able to sign in again.' ?>"
                                                  data-confirm-note="You can change this back at any time."
                                                  data-confirm-action="<?= $member['is_active'] ? 'Deactivate' : 'Reactivate' ?>"
                                                  data-confirm-tone="<?= $member['is_active'] ? 'danger' : 'default' ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="toggle">
                                                <input type="hidden" name="user_id" value="<?= (int)$member['user_id'] ?>">
                                                <button type="submit"
                                                        class="btn btn-sm <?= $member['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                                                    <?= $member['is_active'] ? 'Deactivate' : 'Reactivate' ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if (!$isSelf): ?>
                                            <form method="post" class="d-flex gap-1"
                                                  data-confirm
                                                  data-confirm-title="Reset this password?"
                                                  data-confirm-body="<?= e($member['username']) ?> will need the new password to sign in."
                                                  data-confirm-note="Tell them the new password yourself - it is not emailed."
                                                  data-confirm-action="Reset password"
                                                  data-confirm-tone="default">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="reset_password">
                                                <input type="hidden" name="user_id" value="<?= (int)$member['user_id'] ?>">
                                                <label class="visually-hidden" for="apw<?= (int)$member['user_id'] ?>">
                                                    New password for <?= e($member['username']) ?>
                                                </label>
                                                <input type="password" class="form-control form-control-sm"
                                                       id="apw<?= (int)$member['user_id'] ?>" name="new_password"
                                                       placeholder="New password"
                                                       minlength="<?= sec_password_min() ?>" required>
                                                <button type="submit" class="btn btn-secondary btn-sm">Reset</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <section class="card card-flush">
            <div class="panel-head">
                <h2 class="d-flex align-items-center gap-2"><?= icon('key', 18) ?> What an administrator can do</h2>
            </div>
            <div class="panel-body">
                <p style="margin-top:0">Everything. There is no part of the back office an administrator
                    cannot reach, including creating and deactivating other administrators. Give the role
                    out sparingly &mdash; <a href="<?= BASE_URL ?>/admin/staff.php">Staff</a> covers
                    day-to-day work with far less reach.</p>
                <ul class="perm-list is-denied" style="margin-bottom:0">
                    <li><?= icon('close', 15) ?><span>You cannot deactivate your own account here</span></li>
                    <li><?= icon('close', 15) ?><span>The last active administrator cannot be deactivated</span></li>
                </ul>
            </div>
        </section>
    </div>

    <aside class="summary-card">
        <h2>Add administrator</h2>
        <div class="summary-body">
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create">

                <div class="field-group">
                    <label class="form-label" for="adName">Full name</label>
                    <input type="text" id="adName" name="full_name" class="form-control" required>
                </div>
                <div class="field-group">
                    <label class="form-label" for="adUser">Username</label>
                    <input type="text" id="adUser" name="username" class="form-control"
                           pattern="[A-Za-z0-9_]{3,50}" required>
                    <p class="form-text">3&ndash;50 characters: letters, numbers and underscores.</p>
                </div>
                <div class="field-group">
                    <label class="form-label" for="adEmail">Email</label>
                    <input type="email" id="adEmail" name="email" class="form-control" required>
                </div>
                <div class="field-group">
                    <label class="form-label" for="adContact">Contact number</label>
                    <input type="tel" id="adContact" name="contact_number" class="form-control">
                </div>
                <div class="field-group">
                    <label class="form-label" for="adPass">Temporary password</label>
                    <input type="password" id="adPass" name="password" class="form-control"
                           minlength="<?= sec_password_min() ?>" required>
                    <p class="form-text">
                        <?= sec_password_min() ?>+ characters with an uppercase letter, a lowercase
                        letter, a number and a symbol &mdash; this account can do anything.
                    </p>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <?= icon('plus', 16) ?> Create administrator
                </button>
            </form>
        </div>
    </aside>
</div>

<?php require __DIR__ . '/../includes/backoffice/shell_footer.php'; ?>
