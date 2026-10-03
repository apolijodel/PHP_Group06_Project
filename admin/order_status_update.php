<?php
require_once __DIR__ . '/../includes/auth.php';
require_can('orders.status');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/orders.php');
}
csrf_check();

$orderId = (int)($_POST['order_id'] ?? 0);
$newStatus = $_POST['status'] ?? '';

$transitions = [
    'Pending' => ['Processing', 'Cancelled'],
    'Processing' => ['On Shipping', 'Cancelled'],
    'On Shipping' => ['Completed'],
    'Completed' => [],
    'Cancelled' => [],
];

$stmt = db()->prepare('SELECT status FROM orders WHERE order_id = :id');
$stmt->execute(['id' => $orderId]);
$current = $stmt->fetchColumn();

if ($current === false) {
    flash_set('error', 'Order not found.');
    redirect('/admin/orders.php');
}

if (!in_array($newStatus, $transitions[$current] ?? [], true)) {
    flash_set('error', "Cannot change status from \"$current\" to \"$newStatus\".");
    redirect('/admin/order_view.php?id=' . $orderId);
}

db()->prepare('UPDATE orders SET status = :status WHERE order_id = :id')
    ->execute(['status' => $newStatus, 'id' => $orderId]);

log_activity(
    'order.status',
    'Moved order #' . $orderId . ' from ' . $current . ' to ' . $newStatus,
    'order',
    $orderId
);

flash_set('success', 'Order status updated to ' . $newStatus . '.');
redirect('/admin/order_view.php?id=' . $orderId);
