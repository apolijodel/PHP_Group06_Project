<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_can('products.manage');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/products.php');
}
csrf_check();

$productId = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
$name = trim($_POST['name'] ?? '');
$categoryId = (int)($_POST['category_id'] ?? 0);
$description = trim($_POST['description'] ?? '');
$price = (float)($_POST['price'] ?? 0);
$stock = max(0, (int)($_POST['stock_quantity'] ?? 0));
$isCustomizable = isset($_POST['is_customizable']) ? 1 : 0;

$errors = [];
if ($name === '') $errors[] = 'Product name is required.';
if ($categoryId <= 0) $errors[] = 'Please select a category.';
if ($price <= 0) $errors[] = 'Price must be greater than zero.';

$imagePath = null;
if (!empty($_FILES['image']['name'])) {
    try {
        $imagePath = handle_image_upload($_FILES['image'], __DIR__ . '/../uploads/products');
    } catch (RuntimeException $e) {
        $errors[] = $e->getMessage();
    }
} elseif (!$productId) {
    $errors[] = 'A product image is required.';
}

if ($errors) {
    flash_set('error', implode(' ', $errors));
    redirect($productId ? '/admin/product_form.php?id=' . $productId : '/admin/product_form.php');
}

if ($productId) {
    if ($imagePath) {
        db()->prepare(
            'UPDATE products SET name=:name, category_id=:cat, description=:desc, price=:price,
             stock_quantity=:stock, is_customizable=:cust, image_path=:img WHERE product_id=:id'
        )->execute([
            'name' => $name, 'cat' => $categoryId, 'desc' => $description ?: null,
            'price' => $price, 'stock' => $stock, 'cust' => $isCustomizable,
            'img' => $imagePath, 'id' => $productId,
        ]);
    } else {
        db()->prepare(
            'UPDATE products SET name=:name, category_id=:cat, description=:desc, price=:price,
             stock_quantity=:stock, is_customizable=:cust WHERE product_id=:id'
        )->execute([
            'name' => $name, 'cat' => $categoryId, 'desc' => $description ?: null,
            'price' => $price, 'stock' => $stock, 'cust' => $isCustomizable, 'id' => $productId,
        ]);
    }
    log_activity('product.updated', 'Updated product “' . $name . '”', 'product', $productId);
    flash_set('success', 'Product updated.');
} else {
    // New products start at the store-wide default threshold, which admins
    // set in Settings and can then override per product in Inventory.
    $threshold = max(0, (int)setting('low_stock_default', '5'));

    db()->prepare(
        'INSERT INTO products (category_id, name, description, price, stock_quantity, image_path,
                               is_customizable, low_stock_threshold)
         VALUES (:cat, :name, :desc, :price, :stock, :img, :cust, :threshold)'
    )->execute([
        'cat' => $categoryId, 'name' => $name, 'desc' => $description ?: null,
        'price' => $price, 'stock' => $stock, 'img' => $imagePath, 'cust' => $isCustomizable,
        'threshold' => $threshold,
    ]);
    log_activity(
        'product.created',
        'Added product “' . $name . '” at ' . format_price($price),
        'product',
        (int)db()->lastInsertId()
    );
    flash_set('success', 'Product added.');
}

redirect('/admin/products.php');
