<?php
/**
 * Screen: customers - markup.
 *
 * Rendered inside whichever panel shell required it - the administrator
 * panel and the staff panel share this one copy.
 *
 * Auth and form handling happen in includes/backoffice/screens/customers.php, before any
 * output. Read-only queries that were already here stayed here rather
 * than being moved for the sake of it.
 */
?>
<?php 
$search = trim($_GET['q'] ?? '');
$sql = "SELECT u.*, COUNT(o.order_id) AS order_count,
               COALESCE(SUM(CASE WHEN o.status != 'Cancelled' THEN o.total_amount ELSE 0 END), 0) AS spent
        FROM users u
        LEFT JOIN orders o ON o.user_id = u.user_id
        WHERE u.role = 'customer'";
$params = [];
if ($search !== '') {
    $sql .= ' AND (u.username LIKE :s_user OR u.email LIKE :s_mail OR u.full_name LIKE :s_name)';
    $like = '%' . $search . '%';
    $params['s_user'] = $like;
    $params['s_mail'] = $like;
    $params['s_name'] = $like;
}
$sql .= ' GROUP BY u.user_id ORDER BY u.created_at DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();
?>

<div class="page-head">
    <div>
        <p class="eyebrow eyebrow-muted">People</p>
        <h2 style="margin-top:var(--s-2)">Customers</h2>
        <p><?= count($customers) ?> <?= count($customers) === 1 ? 'account' : 'accounts' ?></p>
    </div>
</div>

<form method="get" class="admin-toolbar" role="search" style="margin-bottom:var(--s-5)">
    <div class="input-icon">
        <label class="visually-hidden" for="customerSearch">Search customers</label>
        <?= icon('search', 18) ?>
        <input type="search" class="form-control" id="customerSearch" name="q" value="<?= e($search) ?>"
               placeholder="Search by name, username or email">
    </div>
    <button type="submit" class="btn btn-primary">Search</button>
    <?php if ($search !== ''): ?>
        <a href="<?= panel_url('customers.php') ?>" class="btn btn-quiet btn-sm">Clear</a>
    <?php endif; ?>
</form>

<div class="data-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <caption class="visually-hidden">Customer accounts</caption>
            <thead>
                <tr>
                    <th scope="col">Customer</th>
                    <th scope="col">Contact</th>
                    <th scope="col">Orders</th>
                    <th scope="col">Spent</th>
                    <th scope="col">Joined</th>
                    <th scope="col">Status</th>
                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $c): ?>
                    <tr>
                        <th scope="row">
                            <span class="d-flex align-items-center gap-2">
                                <span class="avatar" aria-hidden="true"
                                      style="width:32px;height:32px;border-radius:var(--r-pill);background:var(--green);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;flex:none">
                                    <?= e(initials($c['full_name'])) ?>
                                </span>
                                <span>
                                    <span style="display:block;font-weight:600"><?= e($c['full_name']) ?></span>
                                    <span class="text-muted" style="font-size:.8rem;font-weight:400">@<?= e($c['username']) ?></span>
                                </span>
                            </span>
                        </th>
                        <td style="font-size:.85rem">
                            <span style="display:block"><?= e($c['email']) ?></span>
                            <span class="text-muted"><?= e($c['contact_number'] ?: '—') ?></span>
                        </td>
                        <td class="mono-num"><?= (int)$c['order_count'] ?></td>
                        <td class="mono-num" style="font-weight:600"><?= format_price((float)$c['spent']) ?></td>
                        <td class="text-muted" style="font-size:.85rem;white-space:nowrap">
                            <?= e(date('M j, Y', strtotime($c['created_at']))) ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $c['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $c['is_active'] ? 'Active' : 'Deactivated' ?>
                            </span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="<?= panel_url('customer_view.php') ?>?id=<?= (int)$c['user_id'] ?>"
                                   class="btn btn-secondary btn-sm"
                                   aria-label="View <?= e($c['full_name']) ?>">
                                    <?= icon('eye', 14) ?> View
                                </a>
                                <?php if (can('customers.manage')): ?>
                                <form method="post" action="<?= BASE_URL ?>/admin/customer_toggle.php"
                                      data-confirm
                                          data-confirm-title="<?= $c['is_active'] ? 'Deactivate' : 'Reactivate' ?> this customer?"
                                          data-confirm-body="<?= e($c['username']) ?><?= $c['is_active'] ? ' will no longer be able to sign in.' : ' will be able to sign in again.' ?>"
                                          data-confirm-note="You can change this back at any time."
                                          data-confirm-action="<?= $c['is_active'] ? 'Deactivate' : 'Reactivate' ?>"
                                          data-confirm-tone="<?= $c['is_active'] ? 'danger' : 'default' ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="user_id" value="<?= (int)$c['user_id'] ?>">
                                    <button type="submit"
                                            class="btn btn-sm <?= $c['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                                        <?= $c['is_active'] ? 'Deactivate' : 'Reactivate' ?>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$customers): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <span class="empty-icon"><?= icon('users', 24) ?></span>
                                <h3>No customers found</h3>
                                <p><?= $search !== '' ? 'Try a different search term.' : 'Customer accounts will appear here after people register.' ?></p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
