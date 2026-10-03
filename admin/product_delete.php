<?php
require_once __DIR__ . '/../includes/auth.php';
require_can('products.manage');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/products.php');
}
csrf_check();

$productId = (int)($_POST['product_id'] ?? 0);

// Read before the delete: afterwards there is no name left to record.
$nameStmt = db()->prepare('SELECT name FROM products WHERE product_id = :id');
$nameStmt->execute(['id' => $productId]);
$productName = (string)($nameStmt->fetchColumn() ?: 'Unknown product');

try {
    db()->prepare('DELETE FROM products WHERE product_id = :id')->execute(['id' => $productId]);
    log_activity(
        'product.deleted',
        'Deleted product “' . $productName . '”',
        'product',
        $productId
    );
    flash_set('success', 'Product deleted.');
} catch (PDOException $e) {
    flash_set('error', 'Cannot delete this product because it appears in existing cart or order records.');
}

redirect('/admin/products.php');
