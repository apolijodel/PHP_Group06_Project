<?php
/**
 * Screen: dashboard - markup.
 *
 * Rendered inside whichever panel shell required it - the administrator
 * panel and the staff panel share this one copy.
 *
 * Auth and form handling happen in includes/backoffice/screens/dashboard.php, before any
 * output. Read-only queries that were already here stayed here rather
 * than being moved for the sake of it.
 */
?>
<?php 
$counts = [
    'products' => (int)db()->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'categories' => (int)db()->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
    'customers' => (int)db()->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
    'orders' => (int)db()->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
];

// Operational counts — the three things an admin acts on day to day.
$attention = db()->query(
    "SELECT
        SUM(status = 'Pending')   AS pending,
        SUM(status = 'Completed') AS completed
     FROM orders"
)->fetch();

$lowStockTotal = (int)db()->query(
    'SELECT COUNT(*) FROM products WHERE stock_quantity <= low_stock_threshold'
)->fetchColumn();

$salesTotal = (float)db()->query(
    "SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'Cancelled'"
)->fetchColumn();

/**
 * Audit trail. Only the first batch is rendered here; the carousel asks
 * activity_feed.php for the next five when it runs out of cards, so the
 * dashboard never loads a history it might not display.
 *
 * The filters are parsed by the same helper the feed endpoint uses, so paging
 * cannot quietly return rows the filter excludes.
 */
$activityBatch = 5;
$auditFilters = activity_filters($_GET);

$activityStmt = db()->prepare(
    'SELECT * FROM activity_log' . $auditFilters['sql']
    . ' ORDER BY created_at DESC, log_id DESC LIMIT ' . $activityBatch
);
$activityStmt->execute($auditFilters['params']);
$activity = $activityStmt->fetchAll();

// How many events the current filter matches in total, for the caption.
$matchStmt = db()->prepare('SELECT COUNT(*) FROM activity_log' . $auditFilters['sql']);
$matchStmt->execute($auditFilters['params']);
$auditMatches = (int)$matchStmt->fetchColumn();

$auditMoreQuery = activity_filter_query($auditFilters);
$auditRoles = [
    '' => 'Everyone',
    'customer' => 'Customers',
    'staff' => 'Staff',
    'admin' => 'Admins',
];

// Sales overview — grouped straight off orders.created_at / total_amount.
$monthRows = db()->query(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COALESCE(SUM(total_amount), 0) AS revenue
     FROM orders WHERE status != 'Cancelled'
       AND created_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
     GROUP BY ym"
)->fetchAll();
$revenueByMonth = array_column($monthRows, 'revenue', 'ym');

$months = [];
for ($i = 5; $i >= 0; $i--) {
    $key = date('Y-m', strtotime("-$i month"));
    $months[$key] = (float)($revenueByMonth[$key] ?? 0);
}
$peak = max(1, max($months));
$periodTotal = array_sum($months);

// Order status breakdown.
$statusRows = db()->query('SELECT status, COUNT(*) AS n FROM orders GROUP BY status')->fetchAll();
$statusCounts = array_column($statusRows, 'n', 'status');
$statusTotal = max(1, array_sum($statusCounts));
?>

<!-- ------------------------------ stat cards ------------------------------ -->
<div class="stat-row" data-reveal-stagger style="margin-bottom:var(--s-6)">
    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Total orders</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('receipt', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= $counts['orders'] ?></span>
        <span class="stat-note"><a href="<?= panel_url('orders.php') ?>">Manage orders</a></span>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Total customers</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('users', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= $counts['customers'] ?></span>
        <span class="stat-note"><a href="<?= panel_url('customers.php') ?>">View customers</a></span>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Total products</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('package', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= $counts['products'] ?></span>
        <span class="stat-note">
            <a href="<?= panel_url('products.php') ?>"><?= can('products.manage') ? 'Manage catalog' : 'View catalog' ?></a>
        </span>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Total categories</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('tag', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= $counts['categories'] ?></span>
        <span class="stat-note">
            <?php if (can('categories.manage')): ?>
                <a href="<?= BASE_URL ?>/admin/categories.php">Manage categories</a>
            <?php else: ?>In the catalog<?php endif; ?>
        </span>
    </div>
</div>

<!-- --------------------------- needs attention --------------------------- -->
<div class="stat-row" data-reveal-stagger style="margin-bottom:var(--s-6)">
    <div class="stat-card is-accent">
        <div class="stat-top">
            <span class="stat-label">Pending orders</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('clock', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= (int)$attention['pending'] ?></span>
        <span class="stat-note">
            <a href="<?= panel_url('orders.php') ?>?status=Pending">Start processing</a>
        </span>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Low stock products</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('alert', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= $lowStockTotal ?></span>
        <span class="stat-note">
            <?php if ($lowStockTotal): ?>
                <a href="<?= panel_url('inventory.php') ?>?state=low">Restock now</a>
            <?php else: ?>All well stocked<?php endif; ?>
        </span>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Completed orders</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('check-circle', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= (int)$attention['completed'] ?></span>
        <span class="stat-note">Delivered to customers</span>
    </div>

    <div class="stat-card is-green">
        <div class="stat-top">
            <span class="stat-label">Total sales</span>
            <span class="stat-icon" aria-hidden="true"><?= icon('chart', 18) ?></span>
        </div>
        <span class="stat-value mono-num"><?= format_price($salesTotal) ?></span>
        <span class="stat-note">
            <?php if (can('reports.view')): ?>
                <a href="<?= BASE_URL ?>/admin/reports.php">See reports</a>
            <?php else: ?>Excluding cancelled<?php endif; ?>
        </span>
    </div>
</div>

<div class="cart-grid" style="margin-bottom:var(--s-6)">
    <!-- --------------------------- sales overview --------------------------- -->
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
                     aria-label="Monthly revenue for the last six months: <?= e(implode(', ', array_map(
                         static fn($k, $v) => date('F Y', strtotime($k . '-01')) . ' ' . format_price($v),
                         array_keys($months), $months
                     ))) ?>">
                    <?php foreach ($months as $key => $revenue): ?>
                        <div class="bar-col">
                            <div class="bar" style="--h:<?= round($revenue / $peak * 100, 1) ?>%"></div>
                            <span class="bar-label"><?= e(date('M', strtotime($key . '-01'))) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="chart-legend">
                    <span>Six-month revenue: <strong class="text-soft mono-num"><?= format_price($periodTotal) ?></strong></span>
                    <span>Best month: <strong class="text-soft mono-num"><?= format_price($peak) ?></strong></span>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- --------------------------- order status ---------------------------- -->
    <section class="card card-flush">
        <div class="panel-head"><h2>Order status</h2></div>
        <div class="panel-body">
            <?php if (!$counts['orders']): ?>
                <p class="text-muted" style="margin:0">No orders yet.</p>
            <?php else: ?>
                <div class="status-bars">
                    <?php foreach (all_order_statuses() as $status): ?>
                        <?php
                        $n = (int)($statusCounts[$status] ?? 0);
                        $slug = strtolower(str_replace(' ', '-', $status));
                        ?>
                        <div class="status-bar s-<?= $slug ?>">
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

<!-- ------------------------------ audit trail ------------------------------
     What happened, newest first, from both sides of the site: customers
     registering, ordering and saving designs, staff and admins moving orders,
     restocking and editing the catalog. Five cards at a time - the carousel
     fetches the next five from activity_feed.php only when it needs them. -->
<section class="card card-flush">
    <div class="panel-head">
        <h2>Audit trail</h2>
        <span class="text-muted" style="font-size:.85rem">
            <?= $auditMatches ?> <?= $auditMatches === 1 ? 'event' : 'events' ?>
            <?= $auditFilters['active'] ? 'match this filter' : 'recorded' ?> &middot; newest first
        </span>
    </div>
    <div class="panel-body">
        <!-- Filters. A plain GET form, so it works without JavaScript and the
             filtered view is a URL you can keep or share. -->
        <form method="get" action="<?= panel_url('dashboard.php') ?>#audit"
              class="audit-filters" id="audit">
            <div class="af-field">
                <label class="form-label" for="auditRole">Who</label>
                <select id="auditRole" name="role" class="form-select form-select-sm">
                    <?php foreach ($auditRoles as $value => $label): ?>
                        <option value="<?= e($value) ?>"
                            <?= (string)$auditFilters['role'] === (string)$value ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="af-field">
                <label class="form-label" for="auditFrom">From</label>
                <input type="date" id="auditFrom" name="from" class="form-control form-control-sm"
                       value="<?= e((string)$auditFilters['from']) ?>" max="<?= e(date('Y-m-d')) ?>">
            </div>
            <div class="af-field">
                <label class="form-label" for="auditTo">To</label>
                <input type="date" id="auditTo" name="to" class="form-control form-control-sm"
                       value="<?= e((string)$auditFilters['to']) ?>" max="<?= e(date('Y-m-d')) ?>">
            </div>
            <div class="af-actions">
                <button type="submit" class="btn btn-primary btn-sm">
                    <?= icon('filter', 15) ?> Apply
                </button>
                <?php if ($auditFilters['active']): ?>
                    <a href="<?= panel_url('dashboard.php') ?>#audit" class="btn btn-quiet btn-sm">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (!$activity): ?>
            <div class="empty-state" style="padding:var(--s-8) 0">
                <span class="empty-icon"><?= icon('list', 24) ?></span>
                <h3><?= $auditFilters['active'] ? 'No events match this filter' : 'Nothing has happened yet' ?></h3>
                <p>
                    <?= $auditFilters['active']
                        ? 'Try a wider date range, or a different role.'
                        : 'Sign-ins, orders, stock changes and catalog edits will appear here as they happen.' ?>
                </p>
            </div>
        <?php else: ?>
            <div class="carousel admin-carousel" data-carousel data-per-view="5 3 1"
                 data-min-card="180"
                 data-carousel-item=".ad-card"
                 data-carousel-more="<?= BASE_URL ?>/admin/activity_feed.php<?=
                     $auditMoreQuery ? '?' . e($auditMoreQuery) : '' ?>"
                 data-carousel-loaded="<?= count($activity) ?>">
                <button type="button" class="carousel-nav prev" data-carousel-prev
                        aria-label="Newer events"><?= icon('chevron-left', 18) ?></button>
                <div class="carousel-viewport">
                    <div class="carousel-track" data-carousel-track>
                        <?php foreach ($activity as $event): ?>
                            <?php require __DIR__ . '/../activity_card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="button" class="carousel-nav next" data-carousel-next
                        aria-label="Older events"><?= icon('chevron-right', 18) ?></button>
            </div>
            <p class="form-text" data-carousel-status style="margin-top:var(--s-3)" role="status"></p>
        <?php endif; ?>
    </div>
</section>
