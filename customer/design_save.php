<?php
/**
 * Create or update a saved bookmark design.
 *
 * Reached from the customization studio's "Save design" button. Ownership is
 * re-checked from the database on every update, so posting someone else's
 * saved_design_id cannot overwrite their design.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/customer/my_designs.php');
}
csrf_check();

$userId = (int)current_user()['user_id'];
$productId = (int)($_POST['product_id'] ?? 0);
$savedId = (int)($_POST['saved_design_id'] ?? 0);
$designName = trim($_POST['design_name'] ?? '');
$customText = trim($_POST['custom_text'] ?? '');

$back = '/customer/product.php?id=' . $productId . '#studio';

// ---- the product must exist and actually be customizable -----------------
$stmt = db()->prepare('SELECT * FROM products WHERE product_id = :id');
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if (!$product || !$product['is_customizable']) {
    flash_set('error', 'That product cannot be customized.');
    redirect('/customer/shop.php');
}

// ---- shape and design must be real rows ---------------------------------
$shapeId = !empty($_POST['shape_id']) ? (int)$_POST['shape_id'] : null;
$designId = !empty($_POST['design_id']) ? (int)$_POST['design_id'] : null;

if ($shapeId !== null) {
    // Approved only: posting an id straight at this endpoint must not get a
    // pending or rejected option into a saved design.
    $check = db()->prepare("SELECT COUNT(*) FROM shapes WHERE shape_id = :id AND status = 'approved'");
    $check->execute(['id' => $shapeId]);
    if (!$check->fetchColumn()) {
        $shapeId = null;
    }
}
if ($designId !== null) {
    $check = db()->prepare("SELECT COUNT(*) FROM designs WHERE design_id = :id AND status = 'approved'");
    $check->execute(['id' => $designId]);
    if (!$check->fetchColumn()) {
        $designId = null;
    }
}

if ($designName === '') {
    $designName = $product['name'] . ' design';
}
$designName = mb_substr($designName, 0, 100);

/* Personalized text was removed from the studio. A design being edited keeps
   whatever text it already had - that is existing customer data and deleting it
   because a feature retired would be losing their work - but nothing new is
   accepted, so a posted custom_text is ignored. */
$customText = null;

// ---- the design being edited, if any ------------------------------------
$existing = null;
if ($savedId > 0) {
    $stmt = db()->prepare('SELECT * FROM saved_designs WHERE saved_design_id = :id AND user_id = :uid');
    $stmt->execute(['id' => $savedId, 'uid' => $userId]);
    $existing = $stmt->fetch();

    if (!$existing) {
        flash_set('error', 'That saved design could not be found.');
        redirect('/customer/my_designs.php');
    }
}

// ---- photo: a new upload replaces the old one, otherwise keep it ---------
$imagePath = $existing['custom_image_path'] ?? null;
$replacedImage = null;

$hasNewPhoto = !empty($_FILES['custom_image']['name']);

/* The same rule add_to_cart.php applies, for the same reason: a design and a
   photo cannot share the face of the bookmark. Checked here too because saving
   a design is its own endpoint, reachable without going through the studio. */
if ($designId !== null && ($hasNewPhoto || ($imagePath && ($_POST['remove_image'] ?? '') !== '1'))) {
    flash_set(
        'error',
        'A bookmark can carry a design or your photo, not both. '
        . 'Remove the photo, or choose the plain or photo style instead.'
    );
    redirect($back);
}

if ($hasNewPhoto) {
    try {
        $imagePath = handle_image_upload($_FILES['custom_image'], __DIR__ . '/../uploads/customizations');
        $replacedImage = $existing['custom_image_path'] ?? null;
    } catch (RuntimeException $e) {
        flash_set('error', $e->getMessage());
        redirect($back);
    }
} elseif (($_POST['remove_image'] ?? '') === '1') {
    $replacedImage = $imagePath;
    $imagePath = null;
}

// ---- write --------------------------------------------------------------
if ($existing) {
    db()->prepare(
        'UPDATE saved_designs
            SET product_id = :product_id, design_name = :design_name, shape_id = :shape_id,
                design_id = :design_id, custom_text = :custom_text, custom_image_path = :custom_image_path
          WHERE saved_design_id = :id AND user_id = :uid'
    )->execute([
        'product_id' => $productId,
        'design_name' => $designName,
        'shape_id' => $shapeId,
        'design_id' => $designId,
        'custom_text' => $customText,
        'custom_image_path' => $imagePath,
        'id' => $savedId,
        'uid' => $userId,
    ]);

    $message = 'Design updated.';
} else {
    db()->prepare(
        'INSERT INTO saved_designs
            (user_id, product_id, design_name, shape_id, design_id, custom_text, custom_image_path)
         VALUES (:user_id, :product_id, :design_name, :shape_id, :design_id, :custom_text, :custom_image_path)'
    )->execute([
        'user_id' => $userId,
        'product_id' => $productId,
        'design_name' => $designName,
        'shape_id' => $shapeId,
        'design_id' => $designId,
        'custom_text' => $customText,
        'custom_image_path' => $imagePath,
    ]);

    $savedId = (int)db()->lastInsertId();
    $message = 'Design saved.';
}

// Only now that the row is written is the superseded file safe to remove.
if ($replacedImage) {
    $old = __DIR__ . '/../uploads/customizations/' . basename($replacedImage);
    if (is_file($old)) {
        @unlink($old);
    }
}

log_activity('design.saved', $message . ' “' . $designName . '”', 'saved_design', $savedId);

flash_set('success', $message . ' You can reuse it any time from Saved Designs.');
redirect('/customer/my_designs.php');
