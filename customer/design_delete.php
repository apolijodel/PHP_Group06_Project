<?php
/**
 * Delete one of the current customer's saved designs.
 *
 * The WHERE clause is scoped to the session user, so a posted id belonging to
 * someone else simply matches no row rather than deleting theirs.
 */
require_once __DIR__ . '/../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/customer/my_designs.php');
}
csrf_check();

$userId = (int)current_user()['user_id'];
$savedId = (int)($_POST['saved_design_id'] ?? 0);

$stmt = db()->prepare('SELECT * FROM saved_designs WHERE saved_design_id = :id AND user_id = :uid');
$stmt->execute(['id' => $savedId, 'uid' => $userId]);
$design = $stmt->fetch();

if (!$design) {
    flash_set('error', 'That saved design could not be found.');
    redirect('/customer/my_designs.php');
}

db()->prepare('DELETE FROM saved_designs WHERE saved_design_id = :id AND user_id = :uid')
    ->execute(['id' => $savedId, 'uid' => $userId]);

// The saved design owns its photo outright — carts and orders always hold
// their own copy — so removing the file here cannot affect an existing order.
if (!empty($design['custom_image_path'])) {
    $file = __DIR__ . '/../uploads/customizations/' . basename($design['custom_image_path']);
    if (is_file($file)) {
        @unlink($file);
    }
}

log_activity(
    'design.deleted',
    'Deleted saved design “' . $design['design_name'] . '”',
    'saved_design',
    (int)$design['saved_design_id']
);

flash_set('success', 'Design deleted.');
redirect('/customer/my_designs.php');
