<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$userId = (int)current_user()['user_id'];

/**
 * Status tabs. The keys are what the customer sees; "Pending" also covers the
 * internal "Processing" step, exactly as customer_status() presents it
 * everywhere else, so the tab counts always match the badges on the cards.
 */
$tabs = [
    '' => 'All',
    'Pending' => 'Pending',
    'On Shipping' => 'On Shipping',
    'Completed' => 'Completed',
    'Cancelled' => 'Cancelled',
];
$activeTab = isset($_GET['status']) && isset($tabs[$_GET['status']]) ? $_GET['status'] : '';

/** Stored statuses behind each customer-facing tab. */
$storedFor = [
    'Pending' => ['Pending', 'Processing'],
    'On Shipping' => ['On Shipping'],
    'Completed' => ['Completed'],
    'Cancelled' => ['Cancelled'],
];

$sql = 'SELECT o.*,
               COUNT(oi.order_item_id) AS line_count,
               COALESCE(SUM(oi.quantity), 0) AS unit_count
          FROM orders o
          LEFT JOIN order_items oi ON oi.order_id = o.order_id
         WHERE o.user_id = :uid';
$params = ['uid' => $userId];

if ($activeTab !== '') {
    // Distinct placeholders per value: a named parameter can be bound only
    // once under PDO::ATTR_EMULATE_PREPARES => false.
    $names = [];
    foreach ($storedFor[$activeTab] as $i => $status) {
        $key = 'st' . $i;
        $names[] = ':' . $key;
        $params[$key] = $status;
    }
    $sql .= ' AND o.status IN (' . implode(', ', $names) . ')';
}

$sql .= ' GROUP BY o.order_id ORDER BY o.created_at DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Counts for the tabs, over every order this customer has.
$rawCounts = array_column(
    (static function () use ($userId) {
        $s = db()->prepare('SELECT status, COUNT(*) AS n FROM orders WHERE user_id = :uid GROUP BY status');
        $s->execute(['uid' => $userId]);
        return $s->fetchAll();
    })(),
    'n',
    'status'
);

$tabCounts = ['' => array_sum($rawCounts)];
foreach ($storedFor as $label => $stored) {
    $tabCounts[$label] = array_sum(array_map(
        static fn($status) => (int)($rawCounts[$status] ?? 0),
        $stored
    ));
}

/**
 * A thumbnail and headline product for each order card. Fetched in one query
 * for all orders on the page rather than one query per card.
 */
$previews = [];
if ($orders) {
    $ids = array_column($orders, 'order_id');
    $in = implode(',', array_fill(0, count($ids), '?'));
    $s = db()->prepare(
        "SELECT oi.order_id, oi.product_name_snapshot, oi.quantity, p.image_path
           FROM order_items oi
           LEFT JOIN products p ON p.product_id = oi.product_id
          WHERE oi.order_id IN ($in)
          ORDER BY oi.order_item_id"
    );
    $s->execute($ids);
    foreach ($s->fetchAll() as $row) {
        // Keep only the first line per order — that is the card's headline.
        $previews[$row['order_id']] ??= $row;
    }
}

$pageTitle = 'My Orders';
require __DIR__ . '/../includes/customer/header.php';
?>

<div class="account-page">
    <div>
        <div class="page-head">
            <div>
                <p class="eyebrow">Account</p>
                <h1 style="margin-top:var(--s-2)">My orders</h1>
                <p>Every order you&rsquo;ve placed, newest first.</p>
            </div>
        </div>

        <div class="filter-pills" style="margin-top:0" role="group" aria-label="Filter orders by status">
            <?php foreach ($tabs as $key => $label): ?>
                <a class="pill <?= $activeTab === $key ? 'active' : '' ?>"
                   href="<?= BASE_URL ?>/customer/orders.php<?= $key ? '?status=' . urlencode($key) : '' ?>"
                   <?= $activeTab === $key ? 'aria-current="true"' : '' ?>>
                    <?= e($label) ?> <span class="count"><?= (int)($tabCounts[$key] ?? 0) ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (!$orders): ?>
            <div class="card empty-state">
                <span class="empty-icon"><?= icon('package', 26) ?></span>
                <h2><?= $activeTab === '' ? 'No orders yet' : 'Nothing here' ?></h2>
                <p>
                    <?= $activeTab === ''
                        ? 'When you place an order it will appear here, with its status as it moves along.'
                        : 'You have no orders with this status right now.' ?>
                </p>
                <a href="<?= BASE_URL ?>/customer/<?= $activeTab === '' ? 'index.php#shop' : 'orders.php' ?>"
                   class="btn btn-primary" style="margin-top:var(--s-4)">
                    <?= $activeTab === '' ? 'Browse bookmarks' : 'Show all orders' ?>
                </a>
            </div>
        <?php else: ?>
            <div class="order-card-list">
                <?php foreach ($orders as $order): ?>
                    <?php
                    $oid = (int)$order['order_id'];
                    $preview = $previews[$oid] ?? null;
                    $shown = customer_status($order['status']);
                    $units = (int)$order['unit_count'];
                    $extraLines = max(0, (int)$order['line_count'] - 1);
                    ?>
                    <article class="order-card">
                        <header class="order-card-head">
                            <div>
                                <span class="order-ref mono-num">#<?= e(order_reference($order)) ?></span>
                                <span class="order-date">
                                    <?= e(date('F j, Y', strtotime($order['created_at']))) ?>
                                </span>
                            </div>
                            <span class="badge bg-<?= status_variant($shown) ?>"><?= e(strtoupper($shown)) ?></span>
                        </header>

                        <div class="order-card-body">
                            <span class="ol-media">
                                <?php if ($preview && $preview['image_path']): ?>
                                    <img src="<?= e(upload_url('products', $preview['image_path'])) ?>"
                                         alt="" width="56" height="56" loading="lazy">
                                <?php else: ?>
                                    <span class="ol-media-fallback" aria-hidden="true"><?= icon('package', 20) ?></span>
                                <?php endif; ?>
                            </span>

                            <div class="order-card-main">
                                <p class="ol-name">
                                    <?= $preview ? e($preview['product_name_snapshot']) : 'Order items' ?>
                                </p>
                                <p class="ol-qty">
                                    <?= $units ?> <?= $units === 1 ? 'item' : 'items' ?>
                                    <?php if ($extraLines > 0): ?>
                                        &middot; +<?= $extraLines ?> more
                                        <?= $extraLines === 1 ? 'product' : 'products' ?>
                                    <?php endif; ?>
                                </p>
                            </div>

                            <div class="order-card-total">
                                <span class="lbl">Total</span>
                                <span class="amt mono-num"><?= format_price($order['total_amount']) ?></span>
                            </div>
                        </div>

                        <footer class="order-card-foot">
                            <a href="<?= BASE_URL ?>/customer/order_detail.php?id=<?= $oid ?>"
                               class="btn btn-secondary btn-sm">
                                View Order <?= icon('chevron-right', 14) ?>
                            </a>
                        </footer>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../includes/customer/footer.php'; ?>
