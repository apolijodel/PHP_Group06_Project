<?php
/**
 * Screen: orders - markup.
 *
 * Rendered inside whichever panel shell required it - the administrator
 * panel and the staff panel share this one copy.
 *
 * Auth and form handling happen in includes/backoffice/screens/orders.php, before any
 * output. Read-only queries that were already here stayed here rather
 * than being moved for the sake of it.
 */
?>
<?php 
$statusFilter = $_GET['status'] ?? '';
$validStatuses = all_order_statuses();

$sql = 'SELECT o.*, u.username, COALESCE(SUM(oi.quantity), 0) AS unit_count
        FROM orders o
        JOIN users u ON u.user_id = o.user_id
        LEFT JOIN order_items oi ON oi.order_id = o.order_id';
$params = [];
if (in_array($statusFilter, $validStatuses, true)) {
    $sql .= ' WHERE o.status = :status';
    $params['status'] = $statusFilter;
}
$sql .= ' GROUP BY o.order_id ORDER BY o.created_at DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$statusCounts = array_column(
    db()->query('SELECT status, COUNT(*) AS n FROM orders GROUP BY status')->fetchAll(),
    'n',
    'status'
);
$allCount = array_sum($statusCounts);
?>

<div class="page-head">
    <div>
        <p class="eyebrow eyebrow-muted">Sales</p>
        <h2 style="margin-top:var(--s-2)">Orders</h2>
        <p><?= count($orders) ?> <?= count($orders) === 1 ? 'order' : 'orders' ?>
            <?= $statusFilter ? 'with status “' . e($statusFilter) . '”' : 'in total' ?></p>
    </div>
</div>

<div class="filter-pills" style="margin-top:0">
    <a href="<?= panel_url('orders.php') ?>" class="pill <?= !in_array($statusFilter, $validStatuses, true) ? 'active' : '' ?>">
        All <span class="count"><?= (int)$allCount ?></span>
    </a>
    <?php foreach ($validStatuses as $status): ?>
        <a href="<?= panel_url('orders.php') ?>?status=<?= urlencode($status) ?>"
           class="pill <?= $statusFilter === $status ? 'active' : '' ?>">
            <?= e($status) ?> <span class="count"><?= (int)($statusCounts[$status] ?? 0) ?></span>
        </a>
    <?php endforeach; ?>
</div>

<div class="data-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <caption class="visually-hidden">Customer orders</caption>
            <thead>
                <tr>
                    <th scope="col">Order</th>
                    <th scope="col">Customer</th>
                    <th scope="col">Date</th>
                    <th scope="col">Items</th>
                    <th scope="col">Total</th>
                    <th scope="col">Payment</th>
                    <th scope="col">Status</th>
                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <th scope="row" class="mono-num" style="font-weight:600">#<?= (int)$order['order_id'] ?></th>
                        <td><?= e($order['username']) ?></td>
                        <td class="text-muted" style="white-space:nowrap;font-size:.85rem">
                            <?= e(date('M j, Y', strtotime($order['created_at']))) ?>
                        </td>
                        <td class="mono-num text-muted"><?= (int)$order['unit_count'] ?></td>
                        <td class="mono-num" style="font-weight:600"><?= format_price($order['total_amount']) ?></td>
                        <td class="text-muted" style="font-size:.85rem"><?= e($order['payment_method']) ?></td>
                        <td>
                            <span class="badge bg-<?= status_variant($order['status']) ?>"><?= e($order['status']) ?></span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="<?= panel_url('order_view.php') ?>?id=<?= (int)$order['order_id'] ?>"
                                   class="btn btn-secondary btn-sm">
                                    View <?= icon('chevron-right', 14) ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$orders): ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <span class="empty-icon"><?= icon('receipt', 24) ?></span>
                                <h3>No orders found</h3>
                                <p><?= $statusFilter ? 'No orders currently have this status.' : 'Orders will appear here once customers check out.' ?></p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
