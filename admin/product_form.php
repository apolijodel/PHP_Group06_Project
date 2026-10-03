<?php
$productId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$requireCapability = 'products.manage';
$pageTitle = $productId ? 'Edit Product' : 'Add Product';
require __DIR__ . '/includes/admin_header.php';

$product = null;
if ($productId) {
    $stmt = db()->prepare('SELECT * FROM products WHERE product_id = :id');
    $stmt->execute(['id' => $productId]);
    $product = $stmt->fetch() ?: null;
}

$categories = db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
?>

<nav class="breadcrumbs" aria-label="Breadcrumb">
    <a href="<?= BASE_URL ?>/admin/products.php">Products</a>
    <span class="sep" aria-hidden="true"><?= icon('chevron-right', 13) ?></span>
    <span aria-current="page"><?= $product ? e($product['name']) : 'New product' ?></span>
</nav>

<div class="page-head">
    <div>
        <p class="eyebrow eyebrow-muted">Catalog</p>
        <h2 style="margin-top:var(--s-2)"><?= $product ? 'Edit product' : 'Add a product' ?></h2>
        <p><?= $product
            ? 'Changes apply to the storefront immediately. Past orders keep their own snapshots.'
            : 'It goes live in the shop as soon as you save it.' ?></p>
    </div>
</div>

<form method="post" action="<?= BASE_URL ?>/admin/product_save.php" enctype="multipart/form-data"
      class="cart-grid">
    <?= csrf_field() ?>
    <?php if ($product): ?>
        <input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
    <?php endif; ?>

    <div class="card card-flush">
        <section class="form-section">
            <div class="form-section-head">
                <span class="studio-step-num" aria-hidden="true">1</span>
                <h2>Product details</h2>
            </div>
            <div class="form-grid">
                <div class="span-2">
                    <label class="form-label" for="pName">Name</label>
                    <input type="text" id="pName" name="name" class="form-control"
                           value="<?= e($product['name'] ?? '') ?>" required>
                </div>
                <div class="span-2">
                    <label class="form-label" for="pCategory">Category</label>
                    <select id="pCategory" name="category_id" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['category_id'] ?>"
                                <?= ($product['category_id'] ?? null) == $cat['category_id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="span-2">
                    <label class="form-label" for="pDescription">
                        Description <span class="optional">&mdash; shown on the product card and page</span>
                    </label>
                    <textarea id="pDescription" name="description" class="form-control"
                              rows="4"><?= e($product['description'] ?? '') ?></textarea>
                </div>
            </div>
        </section>

        <section class="form-section">
            <div class="form-section-head">
                <span class="studio-step-num" aria-hidden="true">2</span>
                <h2>Price &amp; stock</h2>
            </div>
            <div class="form-grid">
                <div>
                    <label class="form-label" for="pPrice">Price (&#8369;)</label>
                    <input type="number" id="pPrice" name="price" class="form-control" step="0.01" min="0"
                           value="<?= e((string)($product['price'] ?? '')) ?>" required>
                </div>
                <div>
                    <label class="form-label" for="pStock">Stock quantity</label>
                    <input type="number" id="pStock" name="stock_quantity" class="form-control" min="0"
                           value="<?= e((string)($product['stock_quantity'] ?? '0')) ?>" required>
                    <p class="form-text">At five or below it shows in the Inventory view.</p>
                </div>
                <div class="span-2">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_customizable"
                               name="is_customizable" <?= ($product['is_customizable'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_customizable">
                            Customers can personalize this product (shape, design, photo and text)
                        </label>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <aside class="summary-card">
        <h2>Product image</h2>
        <div class="summary-body">
            <?php if ($product): ?>
                <img class="thumb" style="width:100%;aspect-ratio:1/1;display:block"
                     src="<?= upload_url('products', $product['image_path']) ?>"
                     alt="Current image for <?= e($product['name']) ?>">
            <?php endif; ?>
            <div>
                <label class="form-label" for="pImage">
                    <?= $product ? 'Replace image' : 'Upload image' ?>
                </label>
                <input type="file" id="pImage" name="image" class="form-control"
                       accept=".jpg,.jpeg,.png,.webp" <?= $product ? '' : 'required' ?>>
                <p class="form-text">
                    JPG, PNG or WEBP. Max 2MB. Square images look best.
                    <?= $product ? 'Leave empty to keep the current image.' : '' ?>
                </p>
            </div>
        </div>
        <div class="summary-foot">
            <button type="submit" class="btn btn-primary btn-block">
                <?= icon('check', 16) ?> <?= $product ? 'Save changes' : 'Add product' ?>
            </button>
            <a href="<?= BASE_URL ?>/admin/products.php" class="btn btn-secondary btn-block">Cancel</a>
        </div>
    </aside>
</form>

<?php require __DIR__ . '/../includes/backoffice/shell_footer.php'; ?>
