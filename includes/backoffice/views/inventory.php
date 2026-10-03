<?php
/**
 * Screen: inventory - markup.
 *
 * Rendered inside whichever panel shell required it - the administrator
 * panel and the staff panel share this one copy.
 *
 * Auth and form handling happen in includes/backoffice/screens/inventory.php, before any
 * output. Read-only queries that were already here stayed here rather
 * than being moved for the sake of it.
 */
?>
<?php 
$filter = $_GET['state'] ?? '';
$validFilters = ['in', 'low', 'out'];

$where = '';
if ($filter === 'out') {
    $where = 'WHERE p.stock_quantity = 0';
} elseif ($filter === 'low') {
    $where = 'WHERE p.stock_quantity > 0 AND p.stock_quantity <= p.low_stock_threshold';
} elseif ($filter === 'in') {
    $where = 'WHERE p.stock_quantity > p.low_stock_threshold';
}

$products = db()->query(
    "SELECT p.*, c.name AS category_name
       FROM products p
       JOIN categories c ON c.category_id = p.category_id
       $where
      ORDER BY (p.stock_quantity = 0) DESC,
               (p.stock_quantity <= p.low_stock_threshold) DESC,
               p.stock_quantity ASC, p.name ASC"
)->fetchAll();

// Counts for the filter chips, always over the whole catalog.
$tally = db()->query(
    'SELECT
        COUNT(*) AS total,
        SUM(stock_quantity = 0) AS out_count,
        SUM(stock_quantity > 0 AND stock_quantity <= low_stock_threshold) AS low_count,
        SUM(stock_quantity > low_stock_threshold) AS in_count,
        COALESCE(SUM(stock_quantity), 0) AS units
     FROM products'
)->fetch();

$chips = [
    '' => ['All products', (int)$tally['total']],
    'in' => ['In stock', (int)$tally['in_count']],
    'low' => ['Low stock', (int)$tally['low_count']],
    'out' => ['Out of stock', (int)$tally['out_count']],
];
?>

<div class="page-head">
    <div>
        <p class="eyebrow eyebrow-muted">Catalog</p>
        <h2 style="margin-top:var(--s-2)">Inventory</h2>
        <p><?= (int)$tally['total'] ?> <?= (int)$tally['total'] === 1 ? 'product' : 'products' ?>
            &middot; <?= (int)$tally['units'] ?> units on hand</p>
    </div>
    <?php if (can('products.view')): ?>
        <a href="<?= panel_url('products.php') ?>" class="btn btn-secondary">
            <?= icon('package', 16) ?> <?= can('products.manage') ? 'Manage products' : 'View products' ?>
        </a>
    <?php endif; ?>
</div>

<!-- Stock status only. Filtering by category belongs on Products; a
     dropdown here was the same job again in a less obvious control. -->
<div class="filter-pills" style="margin-top:0">
    <?php foreach ($chips as $key => [$label, $count]): ?>
        <?php $isActive = $key === '' ? !in_array($filter, $validFilters, true) : $filter === $key; ?>
        <a class="pill <?= $isActive ? 'active' : '' ?>"
           href="<?= panel_url('inventory.php') ?><?= $key ? '?state=' . $key : '' ?>">
            <?= e($label) ?> <span class="count"><?= $count ?></span>
        </a>
    <?php endforeach; ?>
</div>

<div class="data-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <caption class="visually-hidden">Product stock levels</caption>
            <thead>
                <tr>
                    <th scope="col" colspan="2">Product</th>
                    <th scope="col">Current stock</th>
                    <th scope="col">Low stock threshold</th>
                    <th scope="col">Status</th>
                    <th scope="col">Last updated</th>
                    <th scope="col"><span class="visually-hidden">Action</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <?php
                    $stock = (int)$product['stock_quantity'];
                    $threshold = (int)$product['low_stock_threshold'];
                    [$stockClass, $stockLabel] = stock_state($stock, $threshold);
                    $badge = ['out' => 'danger', 'low' => 'warning', 'in' => 'success'][$stockClass];
                    $statusText = ['out' => 'Out of Stock', 'low' => 'Low Stock', 'in' => 'In Stock'][$stockClass];
                    ?>
                    <tr>
                        <td style="width:64px">
                            <img class="thumb" src="<?= e(upload_url('products', $product['image_path'])) ?>"
                                 width="48" height="48" alt="" loading="lazy">
                        </td>
                        <th scope="row" style="font-weight:600">
                            <?= e($product['name']) ?>
                            <span class="text-muted d-block" style="font-size:.8rem;font-weight:400">
                                <?= e($product['category_name']) ?>
                            </span>
                        </th>
                        <td class="mono-num" style="font-weight:600"><?= $stock ?></td>
                        <td>
                            <?php if (can('inventory.manage')): ?>
                                <form method="post" class="threshold-form">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
                                    <label class="visually-hidden" for="thr<?= (int)$product['product_id'] ?>">
                                        Low stock threshold for <?= e($product['name']) ?>
                                    </label>
                                    <input type="number" class="form-control" id="thr<?= (int)$product['product_id'] ?>"
                                           name="low_stock_threshold" value="<?= $threshold ?>"
                                           min="0" max="10000" step="1">
                                    <button type="submit" class="btn btn-quiet btn-sm">Save</button>
                                </form>
                            <?php else: ?>
                                <span class="mono-num text-muted"><?= $threshold ?></span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-<?= $badge ?>"><?= e($statusText) ?></span></td>
                        <td class="text-muted" style="font-size:.85rem;white-space:nowrap">
                            <?= e(date('M j, Y', strtotime($product['updated_at']))) ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="<?= panel_url('product_restock.php') ?>?id=<?= (int)$product['product_id'] ?>"
                                   class="btn btn-outline-success btn-sm"
                                   aria-label="Restock <?= e($product['name']) ?>">
                                    <?= icon('plus', 14) ?> Restock
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$products): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <span class="empty-icon"><?= icon('layers', 24) ?></span>
                                <h3>Nothing in this view</h3>
                                <p><?= $filter ? 'No products currently have this stock status.' : 'Add a product to start tracking stock.' ?></p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
