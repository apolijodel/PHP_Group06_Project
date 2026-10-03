<?php
require_once __DIR__ . '/../includes/auth.php';
require_can('categories.manage');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/categories.php');
}
csrf_check();

$categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($name === '') {
    flash_set('error', 'Category name is required.');
    redirect('/admin/categories.php');
}

$stmt = db()->prepare('SELECT category_id FROM categories WHERE name = :name AND category_id != :id');
$stmt->execute(['name' => $name, 'id' => $categoryId ?? 0]);
if ($stmt->fetch()) {
    flash_set('error', 'A category with that name already exists.');
    redirect('/admin/categories.php');
}

if ($categoryId) {
    db()->prepare('UPDATE categories SET name = :name, description = :description WHERE category_id = :id')
        ->execute(['name' => $name, 'description' => $description ?: null, 'id' => $categoryId]);
    log_activity(
        'category.updated',
        'Updated category “' . $name . '”',
        'category',
        $categoryId
    );
    flash_set('success', 'Category updated.');
} else {
    db()->prepare('INSERT INTO categories (name, description) VALUES (:name, :description)')
        ->execute(['name' => $name, 'description' => $description ?: null]);
    log_activity(
        'category.created',
        'Added category “' . $name . '”',
        'category',
        (int)db()->lastInsertId()
    );
    flash_set('success', 'Category added.');
}

redirect('/admin/categories.php');
