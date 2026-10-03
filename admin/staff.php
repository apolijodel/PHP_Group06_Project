<?php
/**
 * Staff accounts — administrator only.
 *
 * Without this the staff role could only exist by editing the database by
 * hand, so it is what makes the role usable rather than an extra feature.
 *
 * Deliberately scoped to role = 'staff' in every query. An administrator
 * cannot be listed, edited, deactivated or deleted here, which is exactly the
 * "staff cannot delete administrators / change permissions" rule — enforced by
 * the WHERE clause rather than by hiding a button.
 */
require_once __DIR__ . '/../includes/auth.php';
require_can('staff.manage');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    // ---- create a staff account -----------------------------------------
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
                 VALUES ('staff', :username, :email, :hash, :full_name, :contact, 1)"
            )->execute([
                'username' => $username,
                'email' => $email,
                'hash' => password_hash($password, PASSWORD_DEFAULT),
                'full_name' => $full_name,
                'contact' => $contact ?: null,
            ]);

            log_activity(
                'staff.created',
                'Created staff account ' . $username,
                'user',
                (int)db()->lastInsertId()
            );
            flash_set('success', 'Staff account created for ' . $username . '.');
            redirect('/admin/staff.php');
        }
    }

    // ---- activate / deactivate -------------------------------------------
    if ($action === 'toggle') {
        $staffId = (int)($_POST['user_id'] ?? 0);

        $stmt = db()->prepare("SELECT is_active FROM users WHERE user_id = :id AND role = 'staff'");
        $stmt->execute(['id' => $staffId]);
        $row = $stmt->fetch();

        if ($row) {
            db()->prepare("UPDATE users SET is_active = :s WHERE user_id = :id AND role = 'staff'")
                ->execute(['s' => $row['is_active'] ? 0 : 1, 'id' => $staffId]);
            flash_set('success', $row['is_active']
                ? 'Staff account deactivated. They can no longer sign in.'
                : 'Staff account reactivated.');
        } else {
            flash_set('error', 'Staff account not found.');
        }
        redirect('/admin/staff.php');
    }

    // ---- reset a password -------------------------------------------------
    if ($action === 'reset_password') {
        $staffId = (int)($_POST['user_id'] ?? 0);
        $password = $_POST['new_password'] ?? '';

        if ($failed = password_policy_errors($password)) {
            flash_set('error', password_policy_message($failed));
            redirect('/admin/staff.php');
        }

        $stmt = db()->prepare("UPDATE users SET password_hash = :h WHERE user_id = :id AND role = 'staff'");
        $stmt->execute(['h' => password_hash($password, PASSWORD_DEFAULT), 'id' => $staffId]);

        flash_set($stmt->rowCount() ? 'success' : 'error',
            $stmt->rowCount() ? 'Password reset.' : 'Staff account not found.');
        redirect('/admin/staff.php');
    }
}

$staff = db()->query(
    "SELECT user_id, username, email, full_name, avatar_path, contact_number, is_active, created_at
       FROM users WHERE role = 'staff' ORDER BY created_at DESC"
)->fetchAll();

// Shown on the page so the permissions are documented where they are granted.
$allowed = [
    'View the dashboard',
    'View products and inventory',
    'Restock products',
    'View customers',
    'View orders and update order status',
];
$denied = [
    'Add, edit or delete products',
    'Manage categories, shapes and designs',
    'Change low-stock thresholds',
    'Activate or deactivate customers',
    'View reports or change store settings',
    'Manage staff or administrator accounts',
];

$requireCapability = 'staff.manage';
$pageTitle = 'Staff';
require __DIR__ . '/includes/admin_header.php';
?>

<div class="page-head">
    <div>
        <p class="eyebrow eyebrow-muted">People</p>
        <h2 style="margin-top:var(--s-2)">Staff accounts</h2>
        <p><?= count($staff) ?> <?= count($staff) === 1 ? 'account' : 'accounts' ?> with restricted
            back-office access.</p>
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
                    <caption class="visually-hidden">Staff accounts</caption>
                    <thead>
                        <tr>
                            <th scope="col">Staff member</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Added</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staff as $member): ?>
                            <?php $avatar = avatar_url($member['avatar_path']); ?>
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
                                            <span style="display:block;font-weight:600"><?= e($member['full_name']) ?></span>
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
                                            <label class="visually-hidden" for="pw<?= (int)$member['user_id'] ?>">
                                                New password for <?= e($member['username']) ?>
                                            </label>
                                            <input type="password" class="form-control form-control-sm"
                                                   id="pw<?= (int)$member['user_id'] ?>" name="new_password"
                                                   placeholder="New password"
                                                   minlength="<?= sec_password_min() ?>" required>
                                            <button type="submit" class="btn btn-secondary btn-sm">Reset</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$staff): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <span class="empty-icon"><?= icon('shield', 24) ?></span>
                                        <h3>No staff accounts yet</h3>
                                        <p>Add one to give someone restricted back-office access.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- What the role actually grants, documented where it is granted. -->
        <section class="card card-flush">
            <div class="panel-head">
                <h2 class="d-flex align-items-center gap-2"><?= icon('shield', 18) ?> What staff can do</h2>
            </div>
            <div class="panel-body">
                <div class="form-grid">
                    <div>
                        <p class="eyebrow eyebrow-muted">Allowed</p>
                        <ul class="perm-list is-allowed">
                            <?php foreach ($allowed as $item): ?>
                                <li><?= icon('check', 15) ?><span><?= e($item) ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div>
                        <p class="eyebrow eyebrow-muted">Administrator only</p>
                        <ul class="perm-list is-denied">
                            <?php foreach ($denied as $item): ?>
                                <li><?= icon('close', 15) ?><span><?= e($item) ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <aside class="summary-card">
        <h2>Add staff account</h2>
        <div class="summary-body">
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create">

                <div class="field-group">
                    <label class="form-label" for="sfName">Full name</label>
                    <input type="text" id="sfName" name="full_name" class="form-control" required>
                </div>
                <div class="field-group">
                    <label class="form-label" for="sfUser">Username</label>
                    <input type="text" id="sfUser" name="username" class="form-control"
                           pattern="[A-Za-z0-9_]{3,50}" required>
                    <p class="form-text">3&ndash;50 characters: letters, numbers and underscores.</p>
                </div>
                <div class="field-group">
                    <label class="form-label" for="sfEmail">Email</label>
                    <input type="email" id="sfEmail" name="email" class="form-control" required>
                </div>
                <div class="field-group">
                    <label class="form-label" for="sfContact">Contact number</label>
                    <input type="tel" id="sfContact" name="contact_number" class="form-control">
                </div>
                <div class="field-group">
                    <label class="form-label" for="sfPass">Temporary password</label>
                    <input type="password" id="sfPass" name="password" class="form-control"
                           minlength="<?= sec_password_min() ?>" required>
                    <p class="form-text">
                        <?= sec_password_min() ?>+ characters with an uppercase letter, a lowercase
                        letter, a number and a symbol. They can change it from their profile.
                    </p>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <?= icon('plus', 16) ?> Create staff account
                </button>
            </form>
        </div>
    </aside>
</div>

<?php require __DIR__ . '/../includes/backoffice/shell_footer.php'; ?>
