<?php
/**
 * Change the quantity of one cart line.
 *
 * Answers either a normal form POST (redirect back to the cart) or a fetch
 * (JSON). The JSON branch exists so the cart page can update in place; the
 * form branch is what happens with JavaScript off, and both run exactly the
 * same validation because they are the same code path up to the response.
 *
 * Every number the browser is told comes from the database after the write,
 * never from what the browser sent. A client that asks for 999 of something
 * with 3 in stock is corrected and told so.
 */
require_once __DIR__ . '/../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/customer/cart.php');
}
csrf_check();

$cartItemId = (int)($_POST['cart_item_id'] ?? 0);
$requested = (int)($_POST['quantity'] ?? 1);
$cartId = get_or_create_cart_id(current_user()['user_id']);

/* The line and its product are read inside the transaction that updates them,
   so two quick clicks cannot both read the old stock figure and race. FOR
   UPDATE holds the rows until the commit below. */
$pdo = db();
$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare(
        'SELECT ci.cart_item_id, p.product_id, p.name AS product_name,
                p.price, p.stock_quantity
           FROM cart_items ci
           JOIN products p ON p.product_id = ci.product_id
          WHERE ci.cart_item_id = :id AND ci.cart_id = :cart_id
          FOR UPDATE'
    );
    $stmt->execute(['id' => $cartItemId, 'cart_id' => $cartId]);
    $row = $stmt->fetch();

    if (!$row) {
        $pdo->rollBack();
        if (is_ajax()) {
            $cart = cart_contents();
            json_response([
                'ok' => false,
                'gone' => true,
                'count' => (int)$cart['count'],
                'subtotal' => format_price($cart['subtotal']),
                'total' => format_price($cart['subtotal']),
                'message' => 'That item is no longer in your cart.',
            ], 404);
        }
        flash_set('error', 'Cart item not found.');
        redirect('/customer/cart.php');
    }

    $stock = (int)$row['stock_quantity'];

    /* Out of stock entirely: the line cannot stay at a usable quantity, so say
       so rather than silently setting it to something that will fail at
       checkout. */
    if ($stock < 1) {
        $pdo->rollBack();
        if (is_ajax()) {
            json_response([
                'ok' => false,
                'unavailable' => true,
                'message' => e($row['product_name']) . ' is out of stock.',
            ], 409);
        }
        flash_set('error', $row['product_name'] . ' is out of stock.');
        redirect('/customer/cart.php');
    }

    $quantity = max(1, $requested);
    $capped = false;
    if ($quantity > $stock) {
        $quantity = $stock;
        $capped = true;
    }

    $pdo->prepare('UPDATE cart_items SET quantity = :qty WHERE cart_item_id = :id')
        ->execute(['qty' => $quantity, 'id' => $cartItemId]);

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // The reason goes to the server log, never to the page.
    error_log('update_cart: ' . $e->getMessage());
    if (is_ajax()) {
        json_response(['ok' => false, 'message' => 'Could not update the cart.'], 500);
    }
    flash_set('error', 'Could not update the cart. Please try again.');
    redirect('/customer/cart.php');
}

if (is_ajax()) {
    /* Recomputed from the database through the same helper the cart page and
       the drawer render from, so the three can never disagree. */
    $cart = cart_contents();

    json_response([
        'ok' => true,
        'quantity' => $quantity,
        'capped' => $capped,
        'stock' => $stock,
        'lineTotal' => format_price((float)$row['price'] * $quantity),
        'subtotal' => format_price($cart['subtotal']),
        'total' => format_price($cart['subtotal']),
        'count' => (int)$cart['count'],
        'message' => $capped
            ? 'Only ' . $stock . ' left — quantity reduced to match stock.'
            : '',
    ]);
}

if ($capped) {
    flash_set('error', 'Quantity was reduced to match available stock.');
}
redirect('/customer/cart.php');
