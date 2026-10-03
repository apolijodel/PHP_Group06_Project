<?php
require_once __DIR__ . '/../includes/auth.php';
require_can('categories.manage');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/categories.php');
}
csrf_check();

$categoryId = (int)($_POST['category_id'] ?? 0);

$nameStmt = db()->prepare('SELECT name FROM categories WHERE category_id = :id');
$nameStmt->execute(['id' => $categoryId]);
$categoryName = (string)($nameStmt->fetchColumn() ?: 'Unknown category');

/**
 * Checked before attempting, so the answer is a useful sentence rather than
 * a caught database error. The foreign key is still what enforces this - it
 * is RESTRICT and stays that way, because detaching products from their
 * category to force a delete through would leave the catalog inconsistent.
 */
$useStmt = db()->prepare('SELECT COUNT(*) FROM products WHERE category_id = :id');
$useStmt->execute(['id' => $categoryId]);
$inUse = (int)$useStmt->fetchColumn();

if ($inUse > 0) {
    flash_set('error', sprintf(
        'Cannot delete "%s": %d product%s still assigned to it. Move those products to '
        . 'another category first, then delete it.',
        $categoryName,
        $inUse,
        $inUse === 1 ? ' is' : 's are'
    ));
    redirect('/admin/categories.php');
}

try {
    db()->prepare('DELETE FROM categories WHERE category_id = :id')->execute(['id' => $categoryId]);
    log_activity(
        'category.deleted',
        'Deleted category “' . $categoryName . '”',
        'category',
        $categoryId
    );
    flash_set('success', 'Category deleted.');
} catch (PDOException $e) {
    // Foreign key constraint (products still reference this category).
    flash_set('error', 'Cannot delete this category while products are still assigned to it.');
}

redirect('/admin/categories.php');
