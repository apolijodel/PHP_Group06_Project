<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/customer/cart.php');
}
csrf_check();

$cartItemId = (int)($_POST['cart_item_id'] ?? 0);
$cartId = get_or_create_cart_id(current_user()['user_id']);

db()->prepare('DELETE FROM cart_items WHERE cart_item_id = :id AND cart_id = :cart_id')
    ->execute(['id' => $cartItemId, 'cart_id' => $cartId]);

if (is_ajax()) {
    json_response([
        'ok' => true,
        'message' => 'Removed from cart',
        'count' => cart_item_count(),
    ]);
}

flash_set('success', 'Item removed from cart.');
redirect('/customer/cart.php');
