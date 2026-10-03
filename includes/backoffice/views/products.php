<?php
/**
 * Screen: products - markup.
 *
 * Rendered inside whichever panel shell required it - the administrator
 * panel and the staff panel share this one copy.
 *
 * Auth and form handling happen in includes/backoffice/screens/products.php, before any
 * output. Read-only queries that were already here stayed here rather
 * than being moved for the sake of it.
 */
?>
<?php 
$search = trim((string)($_GET['q'] ?? ''));

$where = [];
$params = [];
if ($search !== '') {
    $where[] = '(p.name LIKE :q_name OR c.name LIKE :q_cat)';
    $params['q_name'] = '%' . $search . '%';
    $params['q_cat'] = '%' . $search . '%';
}
if ($categoryFilter > 0) {
    $where[] = 'p.category_id = :cat';
    $params['cat'] = $categoryFilter;
}

$sql = 'SELECT p.*, c.name AS category_name FROM products p
        JOIN categories c ON c.category_id = p.category_id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
// Within one category, alphabetical reads better than newest-first.
$sql .= $categoryFilter > 0
    ? ' ORDER BY p.name ASC'
    : ' ORDER BY p.created_at DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

/**
 * Counts for the pills. They respect the current search but not the current
 * category, so each number is what you would actually get by clicking that
 * pill - never a count that disagrees with the list below it.
 */
$countSql = 'SELECT p.category_id, COUNT(*) AS n
               FROM products p
               JOIN categories c ON c.category_id = p.category_id';
$countParams = [];
if ($search !== '') {
    $countSql .= ' WHERE (p.name LIKE :t_name OR c.name LIKE :t_cat)';
    $countParams = ['t_name' => '%' . $search . '%', 't_cat' => '%' . $search . '%'];
}
$countSql .= ' GROUP BY p.category_id';
$countStmt = db()->prepare($countSql);
$countStmt->execute($countParams);
$perCategory = array_column($countStmt->fetchAll(), 'n', 'category_id');

$categoryChips = [0 => ['All products', array_sum($perCategory)]];
foreach ($categories as $c) {
    $categoryChips[(int)$c['category_id']] = [
        $c['name'],
        (int)($perCategory[$c['category_id']] ?? 0),
    ];
}

/** Keeps the search box and the category pills from clearing each other. */
$filterUrl = static function (int $category) use ($search): string {
    $qs = array_filter(
        ['category' => $category > 0 ? (string)$category : '', 'q' => $search],
        static fn($v) => $v !== ''
    );
    return panel_url('products.php' . ($qs ? '?' . http_build_query($qs) : ''));
};

$headTitle = $categoryFilter > 0 ? $categoryChips[$categoryFilter][0] : 'All products';
$headNote = $categoryFilter > 0 ? 'in this category' : 'in the catalog';
?>

<div class="page-head">
    <div>
        <p class="eyebrow eyebrow-muted">Catalog</p>
        <h2 style="margin-top:var(--s-2)"><?= e($headTitle) ?></h2>
        <p>
            <?= count($products) ?> <?= count($products) === 1 ? 'product' : 'products' ?>
            <?= e($headNote) ?><?php if ($search !== ''): ?>
                matching &ldquo;<?= e($search) ?>&rdquo;<?php endif; ?>
        </p>
    </div>
    <?php if (can('products.manage')): ?>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= BASE_URL ?>/admin/product_form.php" class="btn btn-primary">
                <?= icon('plus', 16) ?> Add product
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Category pills. Stock status lives on Inventory; filtering the catalog
     the same way in both places made one screen a copy of the other. -->
<div class="filter-pills" style="margin-top:0" role="group" aria-label="Filter products by category">
    <?php foreach ($categoryChips as $id => [$label, $count]): ?>
        <a class="pill <?= $categoryFilter === $id ? 'active' : '' ?>" href="<?= e($filterUrl($id)) ?>"
           <?= $categoryFilter === $id ? 'aria-current="true"' : '' ?>>
            <?= e($label) ?> <span class="count"><?= $count ?></span>
        </a>
    <?php endforeach; ?>
</div>

<form method="get" class="admin-toolbar" role="search" style="margin-bottom:var(--s-5)">
    <?php if ($categoryFilter > 0): ?>
        <input type="hidden" name="category" value="<?= (int)$categoryFilter ?>">
    <?php endif; ?>
    <div class="input-icon">
        <label class="visually-hidden" for="adminProductSearch">Search products</label>
        <?= icon('search', 18) ?>
        <input type="search" class="form-control" id="adminProductSearch" name="q"
               value="<?= e($search) ?>" placeholder="Search by product or category name">
    </div>
    <button type="submit" class="btn btn-primary">Search</button>
    <?php /* Only rendered when something is actually set. Focusing the field
             changes nothing: this is server-side state, not a focus handler. */ ?>
    <?php if ($search !== '' || $categoryFilter > 0): ?>
        <a href="<?= panel_url('products.php') ?>" class="btn btn-quiet btn-sm">
            <?= icon('close', 14) ?> Clear<?= $search !== '' && $categoryFilter > 0 ? ' all' : '' ?>
        </a>
    <?php endif; ?>
</form>

<div class="data-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <caption class="visually-hidden">Product catalog</caption>
            <thead>
                <tr>
                    <th scope="col" colspan="2">Product</th>
                    <th scope="col">Category</th>
                    <th scope="col">Price</th>
                    <th scope="col">Stock</th>
                    <th scope="col">Type</th>
                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <?php $stock = (int)$product['stock_quantity']; ?>
                    <tr>
                        <td style="width:64px">
                            <img class="thumb" src="<?= upload_url('products', $product['image_path']) ?>"
                                 width="48" height="48" alt="" loading="lazy">
                        </td>
                        <th scope="row" style="font-weight:600"><?= e($product['name']) ?></th>
                        <td><span class="badge badge-cat"><?= e($product['category_name']) ?></span></td>
                        <td class="mono-num" style="font-weight:600"><?= format_price($product['price']) ?></td>
                        <td>
                            <?php if ($stock === 0): ?>
                                <span class="badge bg-danger">Out of stock</span>
                            <?php elseif ($stock <= (int)$product['low_stock_threshold']): ?>
                                <span class="badge bg-warning"><?= $stock ?> left</span>
                            <?php else: ?>
                                <span class="mono-num"><?= $stock ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted" style="font-size:.85rem">
                            <?= $product['is_customizable'] ? 'Customizable' : 'Ready-made' ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="<?= BASE_URL ?>/customer/product.php?id=<?= (int)$product['product_id'] ?>"
                                   class="btn btn-secondary btn-sm"
                                   aria-label="View <?= e($product['name']) ?> on the storefront">
                                    <?= icon('eye', 14) ?> View
                                </a>
                                <?php if (can('products.manage')): ?>
                                    <a href="<?= BASE_URL ?>/admin/product_form.php?id=<?= (int)$product['product_id'] ?>"
                                       class="btn btn-secondary btn-sm" aria-label="Edit <?= e($product['name']) ?>">
                                        <?= icon('edit', 14) ?> Edit
                                    </a>
                                <?php endif; ?>
                                <?php if (can('products.manage')): ?>
                                    <form method="post" action="<?= BASE_URL ?>/admin/product_delete.php"
                                          data-confirm
                                          data-confirm-title="Delete product?"
                                          data-confirm-body="Delete &quot;<?= e($product['name']) ?>&quot;?"
                                          data-confirm-action="Delete product">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                                aria-label="Delete <?= e($product['name']) ?>">
                                            <?= icon('trash', 14) ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$products): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <span class="empty-icon"><?= icon('package', 24) ?></span>
                                <h3><?= $search !== '' || $categoryFilter > 0
                                    ? 'Nothing matches those filters' : 'No products yet' ?></h3>
                                <p><?= $search !== '' || $categoryFilter > 0
                                    ? 'Try a different search term or clear the filters.'
                                    : 'Add your first product to start selling.' ?></p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
