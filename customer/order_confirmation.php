<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$orderId = (int)($_GET['order_id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM orders WHERE order_id = :id AND user_id = :uid');
$stmt->execute(['id' => $orderId, 'uid' => current_user()['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    $pageTitle = 'Order not found';
    require __DIR__ . '/../includes/customer/header.php';
    ?>
    <div class="card empty-state">
        <span class="empty-icon"><?= icon('receipt', 26) ?></span>
        <h1>We couldn&rsquo;t find that order</h1>
        <p>It may belong to a different account, or the link might be out of date.</p>
        <a href="<?= BASE_URL ?>/customer/orders.php" class="btn btn-primary" style="margin-top:var(--s-4)">
            View my orders
        </a>
    </div>
    <?php
    require __DIR__ . '/../includes/customer/footer.php';
    exit;
}

// The lines actually ordered. product_id is nullable (a product can be deleted
// later), so the join is LEFT and the image falls back to a placeholder — the
// snapshot columns keep the name and price correct regardless.
$itemStmt = db()->prepare(
    'SELECT oi.*, p.image_path
       FROM order_items oi
       LEFT JOIN products p ON p.product_id = oi.product_id
      WHERE oi.order_id = :id'
);
$itemStmt->execute(['id' => $orderId]);
$items = $itemStmt->fetchAll();

$subtotal = 0.0;
$unitCount = 0;
foreach ($items as $item) {
    $subtotal += (float)$item['line_total'];
    $unitCount += (int)$item['quantity'];
}

$pageTitle = 'Order confirmed';
require __DIR__ . '/../includes/customer/header.php';
?>

<div class="confirm-wrap">
    <span class="confirm-check" aria-hidden="true"><?= icon('check', 38) ?></span>

    <h1>Thank you for your order!</h1>
    <p>We&rsquo;ve received it and started getting your bookmark<?= '' ?> ready. You&rsquo;ll see each status
        change on the order page.</p>

    <dl class="confirm-facts">
        <div>
            <dt>Order number</dt>
            <dd class="mono-num">#<?= e(order_reference($order)) ?></dd>
        </div>
        <div>
            <dt>Order date</dt>
            <dd><?= e(date('M j, Y', strtotime($order['created_at']))) ?></dd>
        </div>
        <div>
            <dt>Items</dt>
            <dd class="mono-num"><?= $unitCount ?></dd>
        </div>
    </dl>

    <div class="card card-pad" style="text-align:left;margin-bottom:var(--s-8)">
        <h2 class="confirm-sub">Order summary</h2>
        <?php
        $lines = array_map(static fn($item) => [
            'image' => $item['image_path'] ? upload_url('products', $item['image_path']) : null,
            'name' => $item['product_name_snapshot'],
            'custom' => customization_summary($item),
            'qty' => (int)$item['quantity'],
            'unit' => (float)$item['unit_price'],
            'total' => (float)$item['line_total'],
        ], $items);
        require __DIR__ . '/../includes/customer/order_lines.php';
        ?>

        <div class="summary-row" style="margin-top:var(--s-4)">
            <span>Subtotal (<?= $unitCount ?> <?= $unitCount === 1 ? 'item' : 'items' ?>)</span>
            <span class="val mono-num"><?= format_price($subtotal) ?></span>
        </div>
        <div class="summary-row muted">
            <span>Shipping</span>
            <span class="val">Free</span>
        </div>
        <div class="summary-total" style="margin-bottom:var(--s-5)">
            <span class="lbl">Total</span>
            <span class="amt mono-num"><?= format_price($order['total_amount']) ?></span>
        </div>

        <div class="summary-row">
            <span>Payment method</span>
            <span class="val"><?= e($order['payment_method']) ?></span>
        </div>
        <div class="summary-row" style="margin-top:var(--s-3)">
            <span>Status</span>
            <span class="val">
                <span class="badge bg-<?= status_variant(customer_status($order['status'])) ?>"><?= e(customer_status($order['status'])) ?></span>
            </span>
        </div>
        <div class="summary-row" style="margin-top:var(--s-3)">
            <span>Delivering to</span>
            <span class="val" style="text-align:right;max-width:28ch"><?= e($order['delivery_address']) ?></span>
        </div>
        <?php if ($order['payment_method'] === 'Bank Transfer'): ?>
            <p class="summary-note" style="margin-top:var(--s-4)">
                <?= icon('bank', 15) ?>
                <span>We&rsquo;ll send our bank details to you shortly. Your bookmark goes into production
                    once we&rsquo;ve confirmed your transfer.</span>
            </p>
        <?php endif; ?>
    </div>

    <div class="confirm-actions">
        <a href="<?= BASE_URL ?>/customer/orders.php" class="btn btn-primary btn-lg">
            <?= icon('package', 18) ?> View My Orders
        </a>
        <a href="<?= BASE_URL ?>/customer/index.php#shop" class="btn btn-secondary btn-lg">
            Continue Shopping
        </a>
    </div>
</div>

<?php require __DIR__ . '/../includes/customer/footer.php'; ?>
