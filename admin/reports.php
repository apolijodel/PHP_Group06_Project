<?php
/**
 * Reports.
 *
 * Deliberately simple: totals, a month-by-month revenue bar chart, a status
 * breakdown and a top-products table. Every number is queried live — there are
 * no hardcoded figures and no analytics the schema cannot support.
 *
 * Cancelled orders are excluded from money totals throughout, so "total sales"
 * means revenue that was actually committed to.
 */
$requireCapability = 'reports.view';
$pageTitle = 'Reports';
require __DIR__ . '/includes/admin_header.php';

/* ---- date range ---------------------------------------------------------
   One range, applied to every figure on the page. Built once here so the
   headline totals, the status split and the product table always describe the
   same set of orders - a filter that moves some numbers and not others is
   worse than no filter at all.

   Only a real calendar date counts; anything else is dropped rather than
   guessed at, and the bounds are swapped if they arrive backwards. */
$asDate = static function (string $value): string {
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return ($d && $d->format('Y-m-d') === $value) ? $value : '';
};
$fFrom = $asDate((string)($_GET['from'] ?? ''));
$fTo = $asDate((string)($_GET['to'] ?? ''));
if ($fFrom !== '' && $fTo !== '' && $fFrom > $fTo) {
    [$fFrom, $fTo] = [$fTo, $fFrom];
}
$hasRange = $fFrom !== '' || $fTo !== '';

$rangeSql = '';
$rangeParams = [];
if ($fFrom !== '') {
    $rangeSql .= ' AND o.created_at >= :from';
    $rangeParams['from'] = $fFrom . ' 00:00:00';
}
if ($fTo !== '') {
    $rangeSql .= ' AND o.created_at <= :to';
    $rangeParams['to'] = $fTo . ' 23:59:59';
}
/** The same conditions for queries whose orders table is not aliased. */
$rangeSqlBare = str_replace('o.created_at', 'created_at', $rangeSql);

$runRange = static function (string $sql, array $params) {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
};

// ---- headline totals -----------------------------------------------------
$totals = $runRange(
    "SELECT
        COUNT(*) AS total_orders,
        COALESCE(SUM(CASE WHEN status <> 'Cancelled' THEN total_amount ELSE 0 END), 0) AS total_sales,
        SUM(status = 'Completed')  AS completed_orders,
        SUM(status = 'Pending')    AS pending_orders,
        SUM(status = 'Cancelled')  AS cancelled_orders
     FROM orders o
     WHERE 1 = 1" . $rangeSql,
    $rangeParams
)->fetch();

$itemsSold = (int)$runRange(
    "SELECT COALESCE(SUM(oi.quantity), 0)
       FROM order_items oi
       JOIN orders o ON o.order_id = oi.order_id
      WHERE o.status <> 'Cancelled'" . $rangeSql,
    $rangeParams
)->fetchColumn();

$paidOrders = (int)$totals['total_orders'] - (int)$totals['cancelled_orders'];
$averageOrder = $paidOrders > 0 ? (float)$totals['total_sales'] / $paidOrders : 0.0;

// ---- revenue for the last six months ------------------------------------
$monthRows = $runRange(
    "SELECT DATE_FORMAT(o.created_at, '%Y-%m') AS ym,
            COALESCE(SUM(o.total_amount), 0) AS revenue,
            COUNT(*) AS orders
       FROM orders o
      WHERE o.status <> 'Cancelled'"
    . ($hasRange ? $rangeSql : " AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)")
    . " GROUP BY ym",
    $hasRange ? $rangeParams : []
)->fetchAll();

$revenueByMonth = array_column($monthRows, 'revenue', 'ym');
$ordersByMonth = array_column($monthRows, 'orders', 'ym');

$months = [];
if ($hasRange) {
    /* Every month the range touches, so a filtered chart has the same shape as
       an unfiltered one rather than collapsing to the months that happen to
       have sales. Capped so a range of years cannot render a thousand bars. */
    $cursor = new DateTime(($fFrom !== '' ? $fFrom : array_key_first($revenueByMonth) . '-01') . ' 00:00:00');
    $cursor->modify('first day of this month');
    $stop = new DateTime(($fTo !== '' ? $fTo : date('Y-m-d')) . ' 00:00:00');
    $stop->modify('first day of this month');
    $guard = 0;
    while ($cursor <= $stop && $guard++ < 36) {
        $key = $cursor->format('Y-m');
        $months[$key] = [
            'revenue' => (float)($revenueByMonth[$key] ?? 0),
            'orders' => (int)($ordersByMonth[$key] ?? 0),
        ];
        $cursor->modify('+1 month');
    }
}
if (!$months) {
    for ($i = 5; $i >= 0; $i--) {
        $key = date('Y-m', strtotime("-$i month"));
        $months[$key] = [
            'revenue' => (float)($revenueByMonth[$key] ?? 0),
            'orders' => (int)($ordersByMonth[$key] ?? 0),
        ];
    }
}
$peak = max(1, max(array_column($months, 'revenue')));
$periodTotal = array_sum(array_column($months, 'revenue'));

// ---- status breakdown ----------------------------------------------------
$statusCounts = array_column(
    $runRange(
        'SELECT status, COUNT(*) AS n FROM orders o WHERE 1 = 1' . $rangeSql . ' GROUP BY status',
        $rangeParams
    )->fetchAll(),
    'n',
    'status'
);
$statusTotal = max(1, array_sum($statusCounts));

// ---- top products --------------------------------------------------------
// Grouped on the snapshot name so a product deleted after being sold still
// shows up in its own row rather than vanishing from the report.
$topProducts = $runRange(
    "SELECT oi.product_name_snapshot AS name,
            SUM(oi.quantity)   AS units,
            SUM(oi.line_total) AS revenue
       FROM order_items oi
       JOIN orders o ON o.order_id = oi.order_id
      WHERE o.status <> 'Cancelled'" . $rangeSql . "
      GROUP BY oi.product_name_snapshot
      ORDER BY revenue DESC, units DESC
      LIMIT 8",
    $rangeParams
)->fetchAll();
$topRevenue = $topProducts ? max(1, (float)$topProducts[0]['revenue']) : 1;
?>

<div class="page-head">
    <div>
        <p class="eyebrow eyebrow-muted">Sales</p>
        <h2 style="margin-top:var(--s-2)">Reports</h2>
        <p>Live figures from the orders table. Cancelled orders are excluded from all money totals.</p>
    </div>
</div>

<?php /* A plain GET form, like the audit trail's. Every figure below is
         recalculated in SQL from this range - nothing on this page is computed
         in the browser - so a filtered report is a URL you can bookmark. */ ?>
<form method="get" class="audit-filters"
      style="border-bottom:0;padding-bottom:0;margin-bottom:var(--s-5)">
    <div class="af-field">
        <label class="form-label" for="rpFrom">From</label>
        <input type="date" id="rpFrom" name="from" class="form-control form-control-sm"
               value="<?= e($fFrom) ?>">
    </div>
    <div class="af-field">
        <label class="form-label" for="rpTo">To</label>
        <input type="date" id="rpTo" name="to" class="form-control form-control-sm"
               value="<?= e($fTo) ?>">
    </div>
    <div class="af-actions">
        <button type="submit" class="btn btn-secondary btn-sm"><?= icon('search', 15) ?> Apply</button>
        <?php if ($hasRange): ?>
            <a class="btn btn-quiet btn-sm" href="<?= BASE_URL ?>/admin/reports.php">Clear</a>
        <?php endif; ?>
    </div>
</form>

<?php if ($hasRange): ?>
    <p class="form-text" style="margin:calc(var(--s-5) * -1) 0 var(--s-5)">
        <?= icon('info', 15) ?>
        Showing orders
        <?= $fFrom !== '' ? 'from ' . e(date('M j, Y', strtotime($fFrom))) : 'up to' ?>
        <?= $fTo !== '' ? ($fFrom !== '' ? 'to ' : '') . e(date('M j, Y', strtotime($fTo))) : 'onwards' ?>.
    </p>
<?php endif; ?>

<!-- ------------------------------ totals ------------------------------ -->
<div class="stat-row" style="margin-bottom:var(--s-6)">
    <div class="stat-card is-green">
        <div class="stat-top">
            <span class="stat-label">Total sales</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('chart', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= format_price((float)$totals['total_sales']) ?></span>
        <span class="stat-note">Across <?= $paidOrders ?> <?= $paidOrders === 1 ? 'order' : 'orders' ?></span>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Total orders</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('receipt', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= (int)$totals['total_orders'] ?></span>
        <span class="stat-note"><?= (int)$totals['cancelled_orders'] ?> cancelled</span>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Average order</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('wallet', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= format_price($averageOrder) ?></span>
        <span class="stat-note"><?= $itemsSold ?> <?= $itemsSold === 1 ? 'item' : 'items' ?> sold</span>
    </div>

    <div class="stat-card is-accent">
        <div class="stat-top">
            <span class="stat-label">Completed</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('check-circle', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= (int)$totals['completed_orders'] ?></span>
        <span class="stat-note"><?= (int)$totals['pending_orders'] ?> still pending</span>
    </div>
</div>

<div class="cart-grid" style="margin-bottom:var(--s-6)">
    <!-- --------------------------- sales chart --------------------------- -->
    <section class="card card-flush">
        <div class="panel-head">
            <h2>Sales overview</h2>
            <span class="text-muted" style="font-size:.85rem">Last 6 months</span>
        </div>
        <div class="panel-body">
            <?php if ($periodTotal <= 0): ?>
                <div class="empty-state" style="padding:var(--s-8) 0">
                    <span class="empty-icon"><?= icon('chart', 24) ?></span>
                    <h3>No sales in this period</h3>
                    <p>Revenue will chart here as orders come in.</p>
                </div>
            <?php else: ?>
                <div class="chart" role="img"
                     aria-label="Monthly revenue: <?= e(implode(', ', array_map(
                         static fn($k, $v) => date('F Y', strtotime($k . '-01')) . ' ' . format_price($v['revenue']),
                         array_keys($months), $months
                     ))) ?>">
                    <?php foreach ($months as $key => $row): ?>
                        <div class="bar-col">
                            <div class="bar" style="--h:<?= round($row['revenue'] / $peak * 100, 1) ?>%"
                                 title="<?= e(date('F Y', strtotime($key . '-01'))) ?>: <?= e(format_price($row['revenue'])) ?>"></div>
                            <span class="bar-label"><?= e(date('M', strtotime($key . '-01'))) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="chart-legend">
                    <span>Six-month revenue:
                        <strong class="text-soft mono-num"><?= format_price($periodTotal) ?></strong></span>
                    <span>Best month:
                        <strong class="text-soft mono-num"><?= format_price($peak) ?></strong></span>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- -------------------------- status split --------------------------- -->
    <section class="card card-flush">
        <div class="panel-head"><h2>Orders by status</h2></div>
        <div class="panel-body">
            <?php if (!(int)$totals['total_orders']): ?>
                <p class="text-muted" style="margin:0">No orders yet.</p>
            <?php else: ?>
                <div class="status-bars">
                    <?php foreach (all_order_statuses() as $status): ?>
                        <?php $n = (int)($statusCounts[$status] ?? 0); ?>
                        <div class="status-bar s-<?= e(strtolower(str_replace(' ', '-', $status))) ?>">
                            <div class="status-bar-top">
                                <span><?= e($status) ?></span>
                                <span class="mono-num text-muted"><?= $n ?></span>
                            </div>
                            <div class="track">
                                <div class="fill" style="--w:<?= round($n / $statusTotal * 100, 1) ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- --------------------------- top products --------------------------- -->
<section class="card card-flush">
    <div class="panel-head">
        <h2>Top products</h2>
        <span class="text-muted" style="font-size:.85rem">By revenue</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <caption class="visually-hidden">Best selling products by revenue</caption>
            <thead>
                <tr>
                    <th scope="col">Product</th>
                    <th scope="col">Units sold</th>
                    <th scope="col">Revenue</th>
                    <th scope="col">Share</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topProducts as $row): ?>
                    <tr>
                        <th scope="row" style="font-weight:600"><?= e($row['name']) ?></th>
                        <td class="mono-num"><?= (int)$row['units'] ?></td>
                        <td class="mono-num" style="font-weight:600"><?= format_price((float)$row['revenue']) ?></td>
                        <td style="min-width:140px">
                            <?php $share = round((float)$row['revenue'] / $topRevenue * 100, 1); ?>
                            <div class="meter" role="img"
                                 aria-label="<?= $share ?>% of the best seller's revenue">
                                <div class="meter-fill" style="--w:<?= $share ?>%"></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$topProducts): ?>
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <span class="empty-icon"><?= icon('chart', 24) ?></span>
                                <h3>Nothing sold yet</h3>
                                <p>Once orders are placed, your best sellers will be listed here.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/../includes/backoffice/shell_footer.php'; ?>
