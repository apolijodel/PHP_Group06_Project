<?php
require_once __DIR__ . '/../includes/auth.php';
require_can('options.manage');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/shapes.php');
}
csrf_check();

$shapeId = (int)($_POST['shape_id'] ?? 0);
db()->prepare('DELETE FROM shapes WHERE shape_id = :id')->execute(['id' => $shapeId]);
flash_set('success', 'Shape deleted.');
redirect('/admin/shapes.php');
