<?php
/**
 * Screen: customer_view - markup.
 *
 * Rendered inside whichever panel shell required it - the administrator
 * panel and the staff panel share this one copy.
 *
 * Auth and form handling happen in includes/backoffice/screens/customer_view.php, before any
 * output. Read-only queries that were already here stayed here rather
 * than being moved for the sake of it.
 */
?>
<?php 
$customerId = (int)($_GET['id'] ?? 0);

$stmt = db()->prepare(
    "SELECT user_id, role, username, email, full_name, avatar_path, contact_number,
            address, city, province, postal_code, is_active, created_at
       FROM users
      WHERE user_id = :id AND role = 'customer'"
);
$stmt->execute(['id' => $customerId]);
$customer = $stmt->fetch();

if (!$customer) {
    ?>
    <div class="card empty-state">
        <span class="empty-icon"><?= icon('users', 26) ?></span>
        <h2>Customer not found</h2>
        <p>That account does not exist, or it is not a customer account.</p>
        <a href="<?= panel_url('customers.php') ?>" class="btn btn-primary" style="margin-top:var(--s-4)">
            Back to customers
        </a>
    </div>
    <?php
    return;   // the panel page closes the shell
}

$stats = db()->prepare(
    "SELECT COUNT(*) AS total_orders,
            COALESCE(SUM(CASE WHEN status <> 'Cancelled' THEN total_amount ELSE 0 END), 0) AS spent,
            SUM(status = 'Pending') AS pending,
            SUM(status = 'Completed') AS completed
       FROM orders WHERE user_id = :uid"
);
$stats->execute(['uid' => $customerId]);
$stats = $stats->fetch();

$orders = db()->prepare(
    'SELECT o.*, COALESCE(SUM(oi.quantity), 0) AS unit_count
       FROM orders o
       LEFT JOIN order_items oi ON oi.order_id = o.order_id
      WHERE o.user_id = :uid
      GROUP BY o.order_id
      ORDER BY o.created_at DESC'
);
$orders->execute(['uid' => $customerId]);
$orders = $orders->fetchAll();

$designCount = db()->prepare('SELECT COUNT(*) FROM saved_designs WHERE user_id = :uid');
$designCount->execute(['uid' => $customerId]);
$designCount = (int)$designCount->fetchColumn();

$avatar = avatar_url($customer['avatar_path']);
?>

<nav class="breadcrumbs" aria-label="Breadcrumb">
    <a href="<?= panel_url('customers.php') ?>">Customers</a>
    <span class="sep" aria-hidden="true"><?= icon('chevron-right', 13) ?></span>
    <span aria-current="page"><?= e($customer['full_name']) ?></span>
</nav>

<div class="page-head">
    <div>
        <p class="eyebrow eyebrow-muted">People</p>
        <h2 style="margin-top:var(--s-2)"><?= e($customer['full_name']) ?></h2>
        <p>@<?= e($customer['username']) ?> &middot; joined
            <?= e(date('M j, Y', strtotime($customer['created_at']))) ?></p>
    </div>
    <?php if (can('customers.manage')): ?>
    <form method="post" action="<?= BASE_URL ?>/admin/customer_toggle.php"
          data-confirm
          data-confirm-title="<?= $customer['is_active'] ? 'Deactivate' : 'Reactivate' ?> this customer?"
          data-confirm-body="<?= e($customer['username']) ?><?= $customer['is_active'] ? ' will no longer be able to sign in.' : ' will be able to sign in again.' ?>"
          data-confirm-note="You can change this back at any time."
          data-confirm-action="<?= $customer['is_active'] ? 'Deactivate' : 'Reactivate' ?>"
          data-confirm-tone="<?= $customer['is_active'] ? 'danger' : 'default' ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="user_id" value="<?= (int)$customer['user_id'] ?>">
        <input type="hidden" name="return_to" value="view">
        <button type="submit" class="btn <?= $customer['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
            <?= $customer['is_active'] ? 'Deactivate account' : 'Reactivate account' ?>
        </button>
    </form>
    <?php endif; ?>
</div>

<div class="stat-row" style="margin-bottom:var(--s-6)">
    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Orders</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('receipt', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= (int)$stats['total_orders'] ?></span>
        <span class="stat-note"><?= (int)$stats['completed'] ?> completed</span>
    </div>
    <div class="stat-card is-green">
        <div class="stat-top">
            <span class="stat-label">Total spent</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('wallet', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= format_price((float)$stats['spent']) ?></span>
        <span class="stat-note">Excluding cancelled</span>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Pending orders</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('clock', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= (int)$stats['pending'] ?></span>
        <span class="stat-note">Awaiting processing</span>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Saved designs</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('palette', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= $designCount ?></span>
        <span class="stat-note">In their library</span>
    </div>
</div>

<div class="cart-grid">
    <!-- ---------------------------- orders ----------------------------- -->
    <section class="card card-flush">
        <div class="panel-head"><h2>Order history</h2></div>
        <div class="table-responsive">
            <table class="table table-hover">
                <caption class="visually-hidden">Orders placed by this customer</caption>
                <thead>
                    <tr>
                        <th scope="col">Order</th>
                        <th scope="col">Date</th>
                        <th scope="col">Items</th>
                        <th scope="col">Total</th>
                        <th scope="col">Status</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <th scope="row" class="mono-num" style="font-weight:600">#<?= (int)$order['order_id'] ?></th>
                            <td class="text-muted" style="font-size:.85rem;white-space:nowrap">
                                <?= e(date('M j, Y', strtotime($order['created_at']))) ?>
                            </td>
                            <td class="mono-num text-muted"><?= (int)$order['unit_count'] ?></td>
                            <td class="mono-num" style="font-weight:600"><?= format_price($order['total_amount']) ?></td>
                            <td>
                                <span class="badge bg-<?= status_variant($order['status']) ?>">
                                    <?= e($order['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= panel_url('order_view.php') ?>?id=<?= (int)$order['order_id'] ?>"
                                       class="btn btn-secondary btn-sm">View</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$orders): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <span class="empty-icon"><?= icon('receipt', 24) ?></span>
                                    <h3>No orders yet</h3>
                                    <p>This customer has not placed an order.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- --------------------------- account ----------------------------- -->
    <aside class="summary-card">
        <h2>Account</h2>
        <div class="summary-body">
            <div class="d-flex align-items-center gap-3" style="margin-bottom:var(--s-5)">
                <?php if ($avatar): ?>
                    <img class="avatar avatar-img" src="<?= e($avatar) ?>" alt="" width="48" height="48"
                         style="width:48px;height:48px;border-radius:var(--r-pill)" loading="lazy" decoding="async">
                <?php else: ?>
                    <span class="avatar" aria-hidden="true"
                          style="width:48px;height:48px;border-radius:var(--r-pill);background:var(--green);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:700;flex:none">
                        <?= e(initials($customer['full_name'])) ?>
                    </span>
                <?php endif; ?>
                <div>
                    <strong style="display:block"><?= e($customer['full_name']) ?></strong>
                    <span class="badge bg-<?= $customer['is_active'] ? 'success' : 'secondary' ?>">
                        <?= $customer['is_active'] ? 'Active' : 'Deactivated' ?>
                    </span>
                </div>
            </div>

            <div class="row-line"><span>Username</span><strong>@<?= e($customer['username']) ?></strong></div>
            <div class="row-line"><span>Email</span><strong><?= e($customer['email']) ?></strong></div>
            <div class="row-line"><span>Contact</span><strong><?= e($customer['contact_number'] ?: '—') ?></strong></div>

            <p class="eyebrow eyebrow-muted" style="margin-top:var(--s-5)">Delivery address</p>
            <p class="text-muted" style="margin-top:var(--s-2);font-size:.9rem">
                <?php $addr = full_address($customer); ?>
                <?= $addr !== '' ? e($addr) : 'No address saved.' ?>
            </p>
        </div>
        <div class="summary-foot">
            <a href="<?= panel_url('customers.php') ?>" class="btn btn-secondary btn-block">
                <?= icon('arrow-left', 16) ?> All customers
            </a>
            <p class="summary-note">
                <?= icon('shield', 15) ?>
                <span>Passwords are stored only as hashes and are never shown here.</span>
            </p>
        </div>
    </aside>
</div>
