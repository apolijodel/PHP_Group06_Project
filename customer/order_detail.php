<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$orderId = (int)($_GET['id'] ?? 0);
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

$itemStmt = db()->prepare('SELECT * FROM order_items WHERE order_id = :id');
$itemStmt->execute(['id' => $orderId]);
$items = $itemStmt->fetchAll();

$cancelled = $order['status'] === 'Cancelled';

// The timeline speaks the same three-word vocabulary as the status badge, so
// the two can never disagree. The internal "Processing" step is still
// reported honestly, as the note under the Pending stage.
$flow = customer_order_flow();
$currentIndex = array_search(customer_status($order['status']), $flow, true);

$stageNotes = [
    'Pending' => $order['status'] === 'Processing'
        ? 'Your bookmark is being crafted by hand.'
        : 'We have your order and will start on it shortly.',
    'On Shipping' => 'Packed and on its way to you.',
    'Completed' => 'Delivered. Enjoy your bookmark!',
];

$pageTitle = 'Order #' . $order['order_id'];
require __DIR__ . '/../includes/customer/header.php';
?>

<div class="account-page">
    <div>
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="<?= BASE_URL ?>/customer/orders.php">My orders</a>
            <span class="sep" aria-hidden="true"><?= icon('chevron-right', 13) ?></span>
            <span aria-current="page">Order #<?= e(order_reference($order)) ?></span>
        </nav>

        <div class="page-head">
            <div>
                <p class="eyebrow">Order</p>
                <h1 class="mono-num" style="margin-top:var(--s-2)">#<?= e(order_reference($order)) ?></h1>
                <p>Placed <?= e(date('M j, Y \a\t g:i A', strtotime($order['created_at']))) ?></p>
            </div>
            <span class="badge bg-<?= status_variant(customer_status($order['status'])) ?>" style="font-size:.85rem;padding:.5em 1em">
                <?= e(customer_status($order['status'])) ?>
            </span>
        </div>

        <div class="cart-grid">
            <div class="d-flex flex-column" style="gap:var(--s-5)">
                <!-- --------------------------- items --------------------------- -->
                <section class="card card-flush">
                    <div class="panel-head">
                        <h2>Items in this order</h2>
                        <span class="text-muted" style="font-size:.85rem"><?= count($items) ?> <?= count($items) === 1 ? 'line' : 'lines' ?></span>
                    </div>
                    <div class="panel-body" style="padding:0">
                        <?php foreach ($items as $item): ?>
                            <div class="cart-item" style="grid-template-columns:minmax(0,1fr) auto">
                                <div class="cart-item-main">
                                    <h3 class="cart-item-name"><?= e($item['product_name_snapshot']) ?></h3>
                                    <?php if ($item['shape_name_snapshot'] || $item['design_name_snapshot'] || $item['custom_text'] || $item['custom_image_path']): ?>
                                        <div class="custom-summary">
                                            <?php if ($item['shape_name_snapshot']): ?>
                                                <span class="custom-tag"><span class="k">Shape</span> <?= e($item['shape_name_snapshot']) ?></span>
                                            <?php endif; ?>
                                            <?php if ($item['design_name_snapshot']): ?>
                                                <span class="custom-tag"><span class="k">Design</span> <?= e($item['design_name_snapshot']) ?></span>
                                            <?php endif; ?>
                                            <?php if ($item['custom_text']): ?>
                                                <span class="custom-tag"><span class="k">Text</span> &ldquo;<?= e($item['custom_text']) ?>&rdquo;</span>
                                            <?php endif; ?>
                                            <?php if ($item['custom_image_path']): ?>
                                                <span class="custom-tag">
                                                    <img src="<?= upload_url('customizations', $item['custom_image_path']) ?>"
                                                         alt="Your uploaded photo">
                                                    Your photo
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <p class="cart-item-unit mono-num" style="margin:0">
                                        <?= format_price($item['unit_price']) ?> &times; <?= (int)$item['quantity'] ?>
                                    </p>
                                </div>
                                <div class="cart-item-side">
                                    <span class="cart-item-total mono-num"><?= format_price($item['line_total']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- ------------------------- delivery -------------------------- -->
                <section class="card card-flush">
                    <div class="panel-head"><h2>Delivery &amp; payment</h2></div>
                    <div class="panel-body">
                        <div class="form-grid">
                            <div>
                                <p class="eyebrow eyebrow-muted">Ships to</p>
                                <p style="margin-top:var(--s-2)">
                                    <strong><?= e($order['full_name']) ?></strong><br>
                                    <span class="text-muted"><?= e($order['contact_number']) ?></span><br>
                                    <span class="text-muted"><?= e($order['delivery_address']) ?></span>
                                </p>
                            </div>
                            <div>
                                <p class="eyebrow eyebrow-muted">Payment</p>
                                <p style="margin-top:var(--s-2)" class="d-flex align-items-center gap-2">
                                    <?= icon($order['payment_method'] === 'Bank Transfer' ? 'bank' : 'wallet', 18) ?>
                                    <strong><?= e($order['payment_method']) ?></strong>
                                </p>
                                <p class="text-muted" style="font-size:.85rem">Nothing was charged online.</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- --------------------------- status --------------------------- -->
            <aside class="summary-card" aria-label="Order status and total">
                <h2>Order status</h2>
                <div class="summary-body">
                    <?php if ($cancelled): ?>
                        <ol class="timeline is-cancelled">
                            <li class="is-done"><span class="t-label">Order placed</span>
                                <span class="t-note"><?= e(date('M j, Y', strtotime($order['created_at']))) ?></span></li>
                            <li class="is-current"><span class="t-label">Cancelled</span>
                                <span class="t-note">This order will not be delivered.</span></li>
                        </ol>
                    <?php else: ?>
                        <ol class="timeline">
                            <?php foreach ($flow as $i => $stage): ?>
                                <?php
                                $state = $i < $currentIndex ? 'is-done' : ($i === $currentIndex ? 'is-current' : '');
                                ?>
                                <li class="<?= $state ?>">
                                    <span class="t-label"><?= e($stage) ?></span>
                                    <span class="t-note"><?= e($stageNotes[$stage] ?? '') ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>

                    <div class="summary-total">
                        <span class="lbl">Order total</span>
                        <span class="amt mono-num"><?= format_price($order['total_amount']) ?></span>
                    </div>
                </div>
                <div class="summary-foot">
                    <a href="<?= BASE_URL ?>/customer/orders.php" class="btn btn-secondary btn-block">
                        <?= icon('arrow-left', 16) ?> All orders
                    </a>
                    <p class="summary-note">
                        <?= icon('mail', 15) ?>
                        <span>Something wrong with this order?
                            <a href="<?= BASE_URL ?>/customer/index.php#contact">Message us</a>.</span>
                    </p>
                </div>
            </aside>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/customer/footer.php'; ?>
