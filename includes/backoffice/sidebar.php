<?php
/**
 * Admin sidebar — identical on every admin page.
 *
 * Dashboard, Products, Categories, Orders, Customers, Inventory, Reports.
 * Shapes and Designs are listed alongside Products because they are real,
 * already-built catalog screens; leaving them out of the nav would make the
 * customization options unreachable rather than simpler.
 *
 * Settings, Profile and Log out are deliberately absent — they all hang off the
 * account menu in the top bar, because they belong to the person signed in
 * rather than to the shop being managed.
 *
 * Every entry points at a page that exists — there are no placeholder links.
 */
$current = basename($_SERVER['SCRIPT_NAME']);

// Counts worth surfacing next to a nav item.
$pendingOrders = (int)db()->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();
$lowStockCount = (int)db()->query(
    'SELECT COUNT(*) FROM products WHERE stock_quantity <= low_stock_threshold'
)->fetchColumn();
$lockedCount = (int)db()->query(
    'SELECT COUNT(*) FROM account_locks WHERE locked_at IS NOT NULL'
)->fetchColumn();
$pendingOptions = (int)db()->query(
    "SELECT (SELECT COUNT(*) FROM shapes WHERE status = 'pending')
          + (SELECT COUNT(*) FROM designs WHERE status = 'pending')"
)->fetchColumn();

/**
 * group label => [file, icon, text, badge, capability]
 *
 * Entries the signed-in role cannot use are dropped below, so staff never see
 * a link that would only refuse them. The page behind each link enforces the
 * same capability itself — this filter is presentation, not protection.
 */
$groups = [
    'Overview' => [
        ['dashboard.php', 'dashboard', 'Dashboard', null, 'dashboard.view'],
    ],
    'Catalog' => [
        ['products.php', 'package', 'Products', null, 'products.view'],
        ['categories.php', 'tag', 'Categories', null, 'categories.manage'],
        ['shapes.php', 'shapes', 'Shapes', null, 'options.manage'],
        ['designs.php', 'palette', 'Designs', null, 'options.manage'],
        ['option_review.php', 'check-circle', 'Review Uploads', $pendingOptions ?: null, 'options.manage'],
        ['inventory.php', 'layers', 'Inventory', $lowStockCount ?: null, 'inventory.view'],
    ],
    'Sales' => [
        ['orders.php', 'receipt', 'Orders', $pendingOrders ?: null, 'orders.view'],
        ['reports.php', 'chart', 'Reports', null, 'reports.view'],
    ],
    'People' => [
        ['customers.php', 'users', 'Customers', null, 'customers.view'],
        ['staff.php', 'shield', 'Staff', null, 'staff.manage'],
        ['admins.php', 'key', 'Admins', null, 'admins.manage'],
    ],
    'Security' => [
        ['security.php', 'shield', 'Security', $lockedCount ?: null, 'security.manage'],
        ['audit_logs.php', 'list', 'Audit Logs', null, 'security.manage'],
    ],
    /* Settings and Profile are not here: they are about the person signed in
       rather than the shop, so they live under their own name in the top bar
       instead of in the list of things to manage. */
];

// Drop links this role cannot follow, then drop groups left empty.
foreach ($groups as $label => $links) {
    $groups[$label] = array_values(array_filter(
        $links,
        static fn($link) => can($link[4])
    ));
    if (!$groups[$label]) {
        unset($groups[$label]);
    }
}
?>
<nav class="admin-side" id="adminSide" aria-label="Admin">
    <a class="brand-mark" href="<?= panel_url('dashboard.php') ?>">
        MarkMe <small><?= e(is_admin() ? 'Admin' : 'Staff') ?></small>
    </a>

    <div class="admin-nav">
        <?php foreach ($groups as $label => $links): ?>
            <span class="group-label"><?= e($label) ?></span>
            <?php foreach ($links as [$href, $ico, $text, $badge, $cap]): ?>
                <?php
                // customer_view.php is a detail page of Customers, and
                // product_form.php / product_restock.php of Products, so they
                // keep their parent highlighted.
                $owners = [
                    'customers.php' => ['customer_view.php'],
                    'products.php' => ['product_form.php', 'product_restock.php'],
                    'orders.php' => ['order_view.php'],
                ];
                $active = $current === $href || in_array($current, $owners[$href] ?? [], true);
                ?>
                <a class="<?= $active ? 'active' : '' ?>" href="<?= panel_url($href) ?>"
                   <?= $active ? 'aria-current="page"' : '' ?>>
                    <?= icon($ico, 18) ?> <?= e($text) ?>
                    <?php if ($badge): ?><span class="badge-mini"><?= (int)$badge ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

</nav>
