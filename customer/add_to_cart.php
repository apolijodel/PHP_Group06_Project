<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/customer/index.php');
}
csrf_check();

$productId = (int)($_POST['product_id'] ?? 0);
$quantity = max(1, (int)($_POST['quantity'] ?? 1));
$savedDesignId = (int)($_POST['saved_design_id'] ?? 0);

$stmt = db()->prepare('SELECT * FROM products WHERE product_id = :id');
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    if (is_ajax()) {
        json_response(['ok' => false, 'message' => 'Product not found.'], 404);
    }
    flash_set('error', 'Product not found.');
    redirect('/customer/index.php');
}

if ($quantity > (int)$product['stock_quantity']) {
    if (is_ajax()) {
        json_response(['ok' => false, 'message' => 'Not enough stock left.'], 409);
    }
    flash_set('error', 'Requested quantity exceeds available stock.');
    redirect('/customer/product.php?id=' . $productId);
}

$shapeId = null;
$designId = null;
$customText = null;
$customImagePath = null;

if ($product['is_customizable']) {
    if ($savedDesignId > 0) {
        // Reusing a saved design. Everything is read back from the database
        // and scoped to the session user, so nothing about the customization
        // — least of all the image path — is taken from the request.
        $stmt = db()->prepare(
            'SELECT * FROM saved_designs WHERE saved_design_id = :id AND user_id = :uid'
        );
        $stmt->execute(['id' => $savedDesignId, 'uid' => current_user()['user_id']]);
        $saved = $stmt->fetch();

        if (!$saved) {
            flash_set('error', 'That saved design could not be found.');
            redirect('/customer/my_designs.php');
        }

        if ((int)$saved['product_id'] !== $productId) {
            flash_set('error', 'That design was made for a different bookmark.');
            redirect('/customer/my_designs.php');
        }

        $shapeId = $saved['shape_id'] !== null ? (int)$saved['shape_id'] : null;
        $designId = $saved['design_id'] !== null ? (int)$saved['design_id'] : null;
        $customText = $saved['custom_text'];

        // The cart line takes its own copy of the photo, so editing or
        // deleting the saved design later cannot alter an order already placed.
        $customImagePath = copy_stored_upload(
            __DIR__ . '/../uploads/customizations',
            $saved['custom_image_path']
        );
    } else {
        /* The ids are checked against the catalog, not taken on trust. The
           studio only renders approved options, but this endpoint is reachable
           directly, and "it is not in the dropdown" is not a control. An id
           that is unknown, pending or rejected is dropped rather than refused,
           so a stale page does not turn into an error the shopper cannot act
           on - they simply get the plain product. */
        $approvedOption = static function (string $table, string $key, int $id): bool {
            $allowed = ['shapes' => 'shape_id', 'designs' => 'design_id'];
            if (!isset($allowed[$table]) || $allowed[$table] !== $key) {
                return false;   // the table and key are ours, never user input
            }
            $stmt = db()->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE {$key} = :id AND status = 'approved'"
            );
            $stmt->execute(['id' => $id]);
            return (int)$stmt->fetchColumn() === 1;
        };

        $shapeId = !empty($_POST['shape_id']) ? (int)$_POST['shape_id'] : null;
        if ($shapeId !== null && !$approvedOption('shapes', 'shape_id', $shapeId)) {
            $shapeId = null;
        }

        $designId = !empty($_POST['design_id']) ? (int)$_POST['design_id'] : null;
        if ($designId !== null && !$approvedOption('designs', 'design_id', $designId)) {
            $designId = null;
        }
        // Same ceiling as design_save.php — the studio posts to both.
        $customText = trim($_POST['custom_text'] ?? '');
        $customText = $customText !== '' ? mb_substr($customText, 0, CUSTOM_TEXT_MAX) : null;

        if (!empty($_FILES['custom_image']['name'])) {
            try {
                $customImagePath = handle_image_upload($_FILES['custom_image'], __DIR__ . '/../uploads/customizations');
            } catch (RuntimeException $e) {
                flash_set('error', $e->getMessage());
                redirect('/customer/product.php?id=' . $productId);
            }
        }
    }
}

$cartId = get_or_create_cart_id(current_user()['user_id']);

$stmt = db()->prepare(
    'INSERT INTO cart_items (cart_id, product_id, quantity, shape_id, design_id, custom_text, custom_image_path)
     VALUES (:cart_id, :product_id, :quantity, :shape_id, :design_id, :custom_text, :custom_image_path)'
);
$stmt->execute([
    'cart_id' => $cartId,
    'product_id' => $productId,
    'quantity' => $quantity,
    'shape_id' => $shapeId,
    'design_id' => $designId,
    'custom_text' => $customText,
    'custom_image_path' => $customImagePath,
]);

// Same work either way; only the reply differs. A fetch() caller gets the
// new cart count so the header badge and drawer can update in place, instead
// of being thrown onto the cart page mid-browse.
if (is_ajax()) {
    json_response([
        'ok' => true,
        'message' => 'Added to cart',
        'count' => cart_item_count(),
    ]);
}

flash_set('success', 'Added to cart.');
redirect('/customer/cart.php');
