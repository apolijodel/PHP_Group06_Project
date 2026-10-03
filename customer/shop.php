<?php
require_once __DIR__ . '/../includes/functions.php';

$categories = db()->query(
    'SELECT c.category_id, c.name, COUNT(p.product_id) AS product_count
     FROM categories c LEFT JOIN products p ON p.category_id = c.category_id
     GROUP BY c.category_id, c.name ORDER BY c.name'
)->fetchAll();

$totalProducts = (int)db()->query('SELECT COUNT(*) FROM products')->fetchColumn();
$priceBounds = db()->query('SELECT MIN(price) AS lo, MAX(price) AS hi FROM products')->fetch()
    ?: ['lo' => 0, 'hi' => 0];

// ---- Filters (all backed by existing columns, no schema change) ----------
$search = trim((string)($_GET['q'] ?? ''));
$activeCategory = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : null;
$customizableOnly = ($_GET['customizable'] ?? '') === '1';
$inStockOnly = ($_GET['in_stock'] ?? '') === '1';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';

$sorts = [
    'newest' => ['Newest first', 'p.created_at DESC'],
    'price_asc' => ['Price: low to high', 'p.price ASC'],
    'price_desc' => ['Price: high to low', 'p.price DESC'],
    'name_asc' => ['Name: A to Z', 'p.name ASC'],
];
$sort = isset($_GET['sort']) && isset($sorts[$_GET['sort']]) ? $_GET['sort'] : 'newest';

$where = [];
$params = [];

if ($search !== '') {
    $where[] = '(p.name LIKE :search_name OR p.description LIKE :search_desc OR c.name LIKE :search_cat)';
    $like = '%' . $search . '%';
    $params['search_name'] = $like;
    $params['search_desc'] = $like;
    $params['search_cat'] = $like;
}
if ($activeCategory) {
    $where[] = 'p.category_id = :category';
    $params['category'] = $activeCategory;
}
if ($customizableOnly) {
    $where[] = 'p.is_customizable = 1';
}
if ($inStockOnly) {
    $where[] = 'p.stock_quantity > 0';
}
if (is_numeric($minPrice)) {
    $where[] = 'p.price >= :min_price';
    $params['min_price'] = (float)$minPrice;
}
if (is_numeric($maxPrice)) {
    $where[] = 'p.price <= :max_price';
    $params['max_price'] = (float)$maxPrice;
}

$sql = 'SELECT p.*, c.name AS category_name FROM products p
        JOIN categories c ON c.category_id = p.category_id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
// Keep sold-out products at the end of whichever sort is chosen.
$sql .= ' ORDER BY (p.stock_quantity > 0) DESC, ' . $sorts[$sort][1];

$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

/** Rebuilds the current query string with one parameter replaced. */
$filterUrl = static function (array $overrides) use ($search, $activeCategory, $sort, $customizableOnly, $inStockOnly, $minPrice, $maxPrice): string {
    $query = array_filter([
        'q' => $search,
        'category' => $activeCategory,
        'sort' => $sort !== 'newest' ? $sort : null,
        'customizable' => $customizableOnly ? '1' : null,
        'in_stock' => $inStockOnly ? '1' : null,
        'min_price' => is_numeric($minPrice) ? $minPrice : null,
        'max_price' => is_numeric($maxPrice) ? $maxPrice : null,
    ], static fn($v) => $v !== null && $v !== '');

    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($query[$key]);
        } else {
            $query[$key] = $value;
        }
    }

    return BASE_URL . '/customer/shop.php' . ($query ? '?' . http_build_query($query) : '');
};

$hasFilters = $search !== '' || $activeCategory || $customizableOnly || $inStockOnly
    || is_numeric($minPrice) || is_numeric($maxPrice);

$pageTitle = 'Shop';
$fullWidth = true;
require __DIR__ . '/../includes/customer/header.php';
?>

<!-- ===================== Shop intro + refine ===================== -->
<section class="section">
    <div class="container">
        <div class="section-head section-head-center">
            <p class="eyebrow">The Shop</p>
            <h1>Every bookmark we make</h1>
            <p>Ready-made classics and fully personalizable designs &mdash; each one cut, printed and
                finished by hand.</p>
        </div>

        <!-- Search / sort / price. Same pill language as the home page, so the
             shop reads as a MarkMe section rather than a filter dashboard. -->
        <form method="get" class="shop-filters" role="search" data-shop-filters>
            <div class="shop-search">
                <div class="input-icon">
                    <label class="visually-hidden" for="shopSearch">Search bookmarks</label>
                    <?= icon('search', 18) ?>
                    <input type="search" class="form-control" id="shopSearch" name="q"
                           value="<?= e($search) ?>" placeholder="Search bookmarks, designs, gift sets&hellip;">
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </div>

            <?php if ($activeCategory): ?>
                <input type="hidden" name="category" value="<?= (int)$activeCategory ?>">
            <?php endif; ?>
            <?php if ($customizableOnly): ?><input type="hidden" name="customizable" value="1"><?php endif; ?>
            <?php if ($inStockOnly): ?><input type="hidden" name="in_stock" value="1"><?php endif; ?>

            <div class="shop-refine">
                <span class="refine-group">
                    <label for="shopSort">Sort</label>
                    <select class="select-quiet" id="shopSort" name="sort">
                        <?php foreach ($sorts as $key => [$label]): ?>
                            <option value="<?= e($key) ?>" <?= $sort === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </span>

                <span class="refine-sep" aria-hidden="true"></span>

                <span class="refine-group">
                    <label for="shopMin">Price</label>
                    <input type="number" class="form-control price-input" id="shopMin" name="min_price"
                           min="0" step="1" placeholder="Min"
                           aria-label="Minimum price"
                           value="<?= e(is_numeric($minPrice) ? (string)$minPrice : '') ?>">
                    <span class="refine-dash" aria-hidden="true">&ndash;</span>
                    <label class="visually-hidden" for="shopMax">Maximum price</label>
                    <input type="number" class="form-control price-input" id="shopMax" name="max_price"
                           min="0" step="1" placeholder="Max"
                           aria-label="Maximum price"
                           value="<?= e(is_numeric($maxPrice) ? (string)$maxPrice : '') ?>">
                    <button type="submit" class="btn btn-quiet btn-sm">Apply</button>
                </span>
            </div>
        </form>

        <!-- Category chips — identical component to the home page. -->
        <div class="filter-pills filter-pills-center">
            <a href="<?= $filterUrl(['category' => null]) ?>" class="pill <?= !$activeCategory ? 'active' : '' ?>">All</a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= $filterUrl(['category' => (int)$cat['category_id']]) ?>"
                   class="pill <?= $activeCategory === (int)$cat['category_id'] ? 'active' : '' ?>">
                    <?= e($cat['name']) ?>
                </a>
            <?php endforeach; ?>
            <span class="pill-divider" aria-hidden="true"></span>
            <a href="<?= $filterUrl(['customizable' => $customizableOnly ? null : '1']) ?>"
               class="pill <?= $customizableOnly ? 'active' : '' ?>">
                <?= icon('wand', 14) ?> Personalizable
            </a>
            <a href="<?= $filterUrl(['in_stock' => $inStockOnly ? null : '1']) ?>"
               class="pill <?= $inStockOnly ? 'active' : '' ?>">
                <?= icon('check', 14) ?> In stock
            </a>
        </div>

        <p class="shop-count">
            <?php if ($hasFilters): ?>
                Showing <strong><?= count($products) ?></strong> of <?= $totalProducts ?>
                <?= $totalProducts === 1 ? 'bookmark' : 'bookmarks' ?><?php
                if ($search !== ''): ?> for &ldquo;<?= e($search) ?>&rdquo;<?php endif; ?>
                &middot; <a href="<?= BASE_URL ?>/customer/shop.php">Clear all</a>
            <?php else: ?>
                <?= count($products) ?> <?= count($products) === 1 ? 'bookmark' : 'bookmarks' ?>,
                from <?= format_price((float)$priceBounds['lo']) ?>
            <?php endif; ?>
        </p>

        <?php if (!$products): ?>
            <div class="card empty-state">
                <span class="empty-icon"><?= icon('search', 26) ?></span>
                <h2>Nothing matches those choices</h2>
                <p>Try a different search, widen the price range, or start again.</p>
                <a href="<?= BASE_URL ?>/customer/shop.php" class="btn btn-primary" style="margin-top:var(--s-4)">
                    Show all bookmarks
                </a>
            </div>
        <?php else: ?>
            <div class="product-grid product-grid-4">
                <?php foreach ($products as $product): require __DIR__ . '/_product_card.php'; endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../includes/customer/footer.php'; ?>
