<?php
/**
 * Screen: products - server-side half.
 *
 * Auth, form handling and data loading, shared by the administrator
 * panel and the staff panel. Runs before either shell opens any
 * output, so redirect() still works.
 */
// Screens run before the panel shell opens, so this loads its own
// dependencies rather than relying on the header that comes after it.
require_once __DIR__ . '/../../auth.php';

/**
 * Category filter. Products are browsed by what they are, not by how much of
 * them is left - stock levels are Inventory's job, and having both screens
 * filter the same way made them two views of one list.
 *
 * The id is checked against the categories table rather than trusted, so what
 * reaches the query is always a category that exists.
 */
$categories = db()->query(
    'SELECT c.category_id, c.name, COUNT(p.product_id) AS product_count
       FROM categories c
       LEFT JOIN products p ON p.category_id = c.category_id
      GROUP BY c.category_id, c.name
      ORDER BY c.name'
)->fetchAll();

$validCategoryIds = array_map('intval', array_column($categories, 'category_id'));
$categoryFilter = isset($_GET['category']) && in_array((int)$_GET['category'], $validCategoryIds, true)
    ? (int)$_GET['category']
    : 0;

$requireCapability = 'products.view';
$pageTitle = 'Products';
