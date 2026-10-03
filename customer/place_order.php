<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/customer/cart.php');
}
csrf_check();

$user = current_user();
$fullName = trim($_POST['full_name'] ?? '');
$contactNumber = trim($_POST['contact_number'] ?? '');
$deliveryAddress = trim($_POST['delivery_address'] ?? '');
$paymentMethod = in_array($_POST['payment_method'] ?? '', ['Cash on Delivery', 'Bank Transfer'], true)
    ? $_POST['payment_method']
    : 'Cash on Delivery';

if ($fullName === '' || $contactNumber === '' || $deliveryAddress === '') {
    flash_set('error', 'Please fill in all delivery details.');
    redirect('/customer/checkout.php');
}

$cartId = get_or_create_cart_id($user['user_id']);
$pdo = db();

try {
    $pdo->beginTransaction();

    // Lock the product rows for the items in this cart so stock can be
    // re-validated safely against concurrent orders before committing.
    $stmt = $pdo->prepare(
        'SELECT ci.cart_item_id, ci.product_id, ci.quantity, ci.shape_id, ci.design_id,
                ci.custom_text, ci.custom_image_path,
                p.name AS product_name, p.price, p.stock_quantity,
                s.name AS shape_name, d.name AS design_name
         FROM cart_items ci
         JOIN products p ON p.product_id = ci.product_id
         LEFT JOIN shapes s ON s.shape_id = ci.shape_id
         LEFT JOIN designs d ON d.design_id = ci.design_id
         WHERE ci.cart_id = :cart_id
         FOR UPDATE'
    );
    $stmt->execute(['cart_id' => $cartId]);
    $items = $stmt->fetchAll();

    if (!$items) {
        throw new RuntimeException('Your cart is empty.');
    }

    foreach ($items as $item) {
        if ((int)$item['quantity'] > (int)$item['stock_quantity']) {
            throw new RuntimeException(
                'Sorry, "' . $item['product_name'] . '" only has ' . $item['stock_quantity'] . ' left in stock. Please update your cart.'
            );
        }
    }

    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    $orderStmt = $pdo->prepare(
        'INSERT INTO orders (user_id, full_name, contact_number, delivery_address, payment_method, total_amount, status)
         VALUES (:user_id, :full_name, :contact_number, :delivery_address, :payment_method, :total_amount, \'Pending\')'
    );
    $orderStmt->execute([
        'user_id' => $user['user_id'],
        'full_name' => $fullName,
        'contact_number' => $contactNumber,
        'delivery_address' => $deliveryAddress,
        'payment_method' => $paymentMethod,
        'total_amount' => $total,
    ]);
    $orderId = (int)$pdo->lastInsertId();

    $itemStmt = $pdo->prepare(
        'INSERT INTO order_items (order_id, product_id, product_name_snapshot, unit_price, quantity,
                                   shape_id, shape_name_snapshot, design_id, design_name_snapshot,
                                   custom_text, custom_image_path, line_total)
         VALUES (:order_id, :product_id, :product_name, :unit_price, :quantity,
                 :shape_id, :shape_name, :design_id, :design_name,
                 :custom_text, :custom_image_path, :line_total)'
    );
    $stockStmt = $pdo->prepare(
        'UPDATE products SET stock_quantity = stock_quantity - :qty WHERE product_id = :id'
    );

    foreach ($items as $item) {
        $itemStmt->execute([
            'order_id' => $orderId,
            'product_id' => $item['product_id'],
            'product_name' => $item['product_name'],
            'unit_price' => $item['price'],
            'quantity' => $item['quantity'],
            'shape_id' => $item['shape_id'],
            'shape_name' => $item['shape_name'],
            'design_id' => $item['design_id'],
            'design_name' => $item['design_name'],
            'custom_text' => $item['custom_text'],
            'custom_image_path' => $item['custom_image_path'],
            'line_total' => $item['price'] * $item['quantity'],
        ]);

        $stockStmt->execute(['qty' => $item['quantity'], 'id' => $item['product_id']]);
    }

    $pdo->prepare('DELETE FROM cart_items WHERE cart_id = :cart_id')->execute(['cart_id' => $cartId]);

    $pdo->commit();

    // After the commit on purpose: the audit trail records what happened, so
    // it must never be able to roll an order back.
    log_activity(
        'order.placed',
        'Placed order #' . $orderId . ' for ' . format_price($total),
        'order',
        $orderId
    );

    redirect('/customer/order_confirmation.php?order_id=' . $orderId);
} catch (RuntimeException $e) {
    $pdo->rollBack();
    flash_set('error', $e->getMessage());
    redirect('/customer/cart.php');
} catch (Throwable $e) {
    $pdo->rollBack();
    flash_set('error', 'An unexpected error occurred while placing your order. Please try again.');
    redirect('/customer/checkout.php');
}
