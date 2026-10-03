<?php
/**
 * Screen: product_restock - server-side half.
 *
 * Auth, form handling and data loading, shared by the administrator
 * panel and the staff panel. Runs before either shell opens any
 * output, so redirect() still works.
 */
require_once __DIR__ . '/../../auth.php';
require_can('products.restock');

$productId = (int)($_GET['id'] ?? $_POST['product_id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM products WHERE product_id = :id');
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    flash_set('error', 'Product not found.');
    redirect('/admin/products.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $addQty = max(0, (int)($_POST['add_quantity'] ?? 0));
    if ($addQty > 0) {
        db()->prepare('UPDATE products SET stock_quantity = stock_quantity + :qty WHERE product_id = :id')
            ->execute(['qty' => $addQty, 'id' => $productId]);
        $newStock = (int)$product['stock_quantity'] + $addQty;
        log_activity(
            'inventory.restocked',
            'Restocked “' . $product['name'] . '” by ' . $addQty . ' to ' . $newStock,
            'product',
            $productId
        );
        flash_set('success', "Added {$addQty} units. New stock: " . $newStock);
    }
    redirect('/admin/products.php');
}

$requireCapability = 'products.restock';
$pageTitle = 'Restock Product';
