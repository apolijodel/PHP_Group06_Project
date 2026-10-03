<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

// Read the profile fresh rather than trusting the session copy: the address
// parts may have been edited (or added) since this session signed in.
$user = current_user();
$profileStmt = db()->prepare('SELECT * FROM users WHERE user_id = :id');
$profileStmt->execute(['id' => $user['user_id']]);
$user = $profileStmt->fetch() ?: $user;

$cartId = get_or_create_cart_id($user['user_id']);

$stmt = db()->prepare(
    'SELECT ci.*, p.name AS product_name, p.price, p.stock_quantity, p.image_path AS product_image,
            s.name AS shape_name, d.name AS design_name
     FROM cart_items ci
     JOIN products p ON p.product_id = ci.product_id
     LEFT JOIN shapes s ON s.shape_id = ci.shape_id
     LEFT JOIN designs d ON d.design_id = ci.design_id
     WHERE ci.cart_id = :cart_id
     ORDER BY ci.created_at DESC'
);
$stmt->execute(['cart_id' => $cartId]);
$items = $stmt->fetchAll();

if (!$items) {
    flash_set('error', 'Your cart is empty.');
    redirect('/customer/cart.php');
}

$subtotal = 0;
$unitCount = 0;
foreach ($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $unitCount += (int)$item['quantity'];
}

// Only the two methods the orders.payment_method enum accepts.
$paymentMethods = [
    'Cash on Delivery' => ['wallet', 'Pay the courier in cash when your order arrives at your door.'],
    'Bank Transfer' => ['bank', 'We send our bank details after you order. Send proof of payment and we start crafting.'],
];

$pageTitle = 'Checkout';
require __DIR__ . '/../includes/customer/header.php';
?>

<div class="page-head">
    <div>
        <p class="eyebrow">Almost there</p>
        <h1 style="margin-top:var(--s-2)">Checkout</h1>
    </div>
    <a href="<?= BASE_URL ?>/customer/cart.php" class="btn btn-secondary btn-sm">
        <?= icon('arrow-left', 15) ?> Back to cart
    </a>
</div>

<ol class="checkout-steps" id="checkoutSteps" aria-label="Checkout progress">
    <li class="is-active" data-step-for="stepShipping">
        <span class="dot" aria-hidden="true">1</span> <span class="step-name">Shipping</span>
    </li>
    <li class="bar" role="presentation" aria-hidden="true"></li>
    <li data-step-for="stepPayment">
        <span class="dot" aria-hidden="true">2</span> <span class="step-name">Payment</span>
    </li>
    <li class="bar" role="presentation" aria-hidden="true"></li>
    <li data-step-for="stepReview">
        <span class="dot" aria-hidden="true">3</span> <span class="step-name">Review</span>
    </li>
</ol>

<form method="post" action="<?= BASE_URL ?>/customer/place_order.php" class="checkout-grid" data-once>
    <?= csrf_field() ?>

    <div class="card card-flush">
        <!-- ----------------------- 01 Shipping ----------------------- -->
        <section class="form-section" id="stepShipping">
            <div class="form-section-head">
                <span class="studio-step-num" aria-hidden="true">1</span>
                <h2>Shipping details</h2>
            </div>

            <div class="form-grid">
                <div>
                    <label class="form-label" for="full_name">Full name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control"
                           value="<?= e($user['full_name']) ?>" autocomplete="name" required>
                </div>
                <div>
                    <label class="form-label" for="contact_number">Contact number</label>
                    <input type="tel" id="contact_number" name="contact_number" class="form-control"
                           value="<?= e($user['contact_number']) ?>" autocomplete="tel" required>
                </div>
                <div class="span-2">
                    <label class="form-label" for="delivery_address">Delivery address</label>
                    <textarea id="delivery_address" name="delivery_address" class="form-control" rows="3"
                              autocomplete="street-address" required><?= e(full_address($user)) ?></textarea>
                    <p class="form-text">House or unit number, street, barangay, city and province.</p>
                </div>
            </div>
        </section>

        <!-- ------------------------ 02 Payment ----------------------- -->
        <section class="form-section" id="stepPayment">
            <div class="form-section-head">
                <span class="studio-step-num" aria-hidden="true">2</span>
                <h2>Payment method</h2>
            </div>

            <fieldset class="pay-options">
                <legend class="visually-hidden">Choose a payment method</legend>
                <?php foreach ($paymentMethods as $method => [$ico, $blurb]): ?>
                    <?php $first = $method === 'Cash on Delivery'; ?>
                    <label class="pay-option <?= $first ? 'selected' : '' ?>">
                        <input type="radio" name="payment_method" value="<?= e($method) ?>" <?= $first ? 'checked' : '' ?>>
                        <span class="pay-radio" aria-hidden="true"></span>
                        <span class="pay-icon" aria-hidden="true"><?= icon($ico, 20) ?></span>
                        <span class="pay-copy">
                            <span class="pay-title"><?= e($method) ?></span>
                            <span class="pay-desc"><?= e($blurb) ?></span>
                        </span>
                    </label>
                <?php endforeach; ?>
            </fieldset>

            <p class="summary-note" style="margin-top:var(--s-4)">
                <?= icon('shield', 15) ?>
                <span>No card details are collected and nothing is charged online.</span>
            </p>
        </section>

        <!-- ------------------------ 03 Review ------------------------ -->
        <section class="form-section" id="stepReview">
            <div class="form-section-head">
                <span class="studio-step-num" aria-hidden="true">3</span>
                <h2>Review your order</h2>
            </div>

            <?php
            // Same partial the confirmation and order pages use, so the
            // customer sees one consistent summary all the way through.
            $lines = array_map(static fn($item) => [
                'image' => upload_url('products', $item['product_image']),
                'name' => $item['product_name'],
                'custom' => customization_summary($item),
                'qty' => (int)$item['quantity'],
                'unit' => (float)$item['price'],
                'total' => (float)$item['price'] * (int)$item['quantity'],
            ], $items);
            require __DIR__ . '/../includes/customer/order_lines.php';
            ?>

            <p class="form-text" style="margin-top:var(--s-4)">
                Need a change? <a href="<?= BASE_URL ?>/customer/cart.php">Edit your cart</a> before placing
                the order.
            </p>
        </section>
    </div>

    <!-- ---------------------------- summary ---------------------------- -->
    <aside class="summary-card" aria-label="Order total">
        <h2>Order total</h2>
        <div class="summary-body">
            <div class="summary-row">
                <span>Subtotal (<?= $unitCount ?> <?= $unitCount === 1 ? 'item' : 'items' ?>)</span>
                <span class="val mono-num"><?= format_price($subtotal) ?></span>
            </div>
            <div class="summary-row muted">
                <span>Shipping</span>
                <span class="val">Free</span>
            </div>
            <div class="summary-total">
                <span class="lbl">Total</span>
                <span class="amt mono-num"><?= format_price($subtotal) ?></span>
            </div>
        </div>
        <div class="summary-foot">
            <button type="submit" class="btn btn-primary btn-lg btn-block" data-busy="Placing order&hellip;">
                <?= icon('check', 18) ?> Place order
            </button>
            <p class="summary-note">
                <?= icon('truck', 15) ?>
                <span>You can track every status change from My Orders.</span>
            </p>
        </div>
    </aside>
</form>

<?php require __DIR__ . '/../includes/customer/footer.php'; ?>
