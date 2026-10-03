<?php
require_once __DIR__ . '/../includes/auth.php';
require_can('options.manage');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/designs.php');
}
csrf_check();

$designId = (int)($_POST['design_id'] ?? 0);
db()->prepare('DELETE FROM designs WHERE design_id = :id')->execute(['id' => $designId]);
flash_set('success', 'Design deleted.');
redirect('/admin/designs.php');
