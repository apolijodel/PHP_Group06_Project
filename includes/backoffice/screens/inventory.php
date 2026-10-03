<?php
/**
 * Screen: inventory - server-side half.
 *
 * Auth, form handling and data loading, shared by the administrator
 * panel and the staff panel. Runs before either shell opens any
 * output, so redirect() still works.
 */
/**
 * Inventory.
 *
 * This is a view of the products table, not a second stock system: "current
 * stock" is products.stock_quantity, the same column the storefront reads and
 * checkout decrements. The only inventory-specific columns are the per-product
 * low-stock threshold and the updated_at timestamp.
 */
// Auth and the POST handler must both run before admin_header.php emits any
// HTML, otherwise the redirect below would fire after headers were sent.
require_once __DIR__ . '/../../auth.php';
require_manager();

// Saving a threshold happens here rather than on the product form, because it
// is an inventory decision rather than a catalog one.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Viewing stock is a staff job; deciding what counts as "low" is a
    // configuration change, so it stays with the administrator.
    require_can('inventory.manage');
    csrf_check();
    $productId = (int)($_POST['product_id'] ?? 0);
    $threshold = max(0, min(10000, (int)($_POST['low_stock_threshold'] ?? 5)));

    $stmt = db()->prepare('UPDATE products SET low_stock_threshold = :t WHERE product_id = :id');
    $stmt->execute(['t' => $threshold, 'id' => $productId]);

    flash_set(
        $stmt->rowCount() ? 'success' : 'error',
        $stmt->rowCount() ? 'Low-stock threshold updated.' : 'Product not found.'
    );
    redirect('/admin/inventory.php');
}

$requireCapability = 'inventory.view';
$pageTitle = 'Inventory';
