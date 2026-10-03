<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$cartId = get_or_create_cart_id(current_user()['user_id']);

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

$subtotal = 0;
$unitCount = 0;
foreach ($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $unitCount += (int)$item['quantity'];
}

$pageTitle = 'My Cart';
require __DIR__ . '/../includes/customer/header.php';
?>

<div class="page-head">
    <div>
        <p class="eyebrow">Your bag</p>
        <h1 style="margin-top:var(--s-2)">Shopping cart</h1>
        <?php if ($items): ?>
            <p><?= $unitCount ?> <?= $unitCount === 1 ? 'item' : 'items' ?> ready to order</p>
        <?php endif; ?>
    </div>
    <a href="<?= BASE_URL ?>/customer/shop.php" class="btn btn-secondary btn-sm">
        <?= icon('arrow-left', 15) ?> Continue shopping
    </a>
</div>

<?php if (!$items): ?>
    <div class="card empty-state">
        <span class="empty-icon"><?= icon('cart', 26) ?></span>
        <h2>Your cart is empty</h2>
        <p>Browse the shop and design a bookmark that is properly yours.</p>
        <a href="<?= BASE_URL ?>/customer/shop.php" class="btn btn-primary" style="margin-top:var(--s-4)">
            Browse bookmarks <?= icon('arrow-right', 16) ?>
        </a>
    </div>
<?php else: ?>
    <div class="cart-grid">
        <!-- ----------------------------- items ----------------------------- -->
        <div class="cart-list">
            <?php foreach ($items as $item): ?>
                <?php
                $lineTotal = $item['price'] * $item['quantity'];
                $itemId = (int)$item['cart_item_id'];
                $maxStock = max(1, (int)$item['stock_quantity']);
                $overStock = (int)$item['quantity'] > (int)$item['stock_quantity'];
                ?>
                <article class="cart-item" data-cart-row>
                    <div class="cart-item-media">
                        <img src="<?= upload_url('products', $item['product_image']) ?>"
                             alt="<?= e($item['product_name']) ?>" loading="lazy">
                    </div>

                    <div class="cart-item-main">
                        <h2 class="cart-item-name">
                            <a href="<?= BASE_URL ?>/customer/product.php?id=<?= (int)$item['product_id'] ?>">
                                <?= e($item['product_name']) ?>
                            </a>
                        </h2>

                        <?php if ($item['shape_name'] || $item['design_name'] || $item['custom_text'] || $item['custom_image_path']): ?>
                            <div class="custom-summary">
                                <?php if ($item['shape_name']): ?>
                                    <span class="custom-tag"><span class="k">Shape</span> <?= e($item['shape_name']) ?></span>
                                <?php endif; ?>
                                <?php if ($item['design_name']): ?>
                                    <span class="custom-tag"><span class="k">Design</span> <?= e($item['design_name']) ?></span>
                                <?php endif; ?>
                                <?php if ($item['custom_text']): ?>
                                    <span class="custom-tag"><span class="k">Text</span> &ldquo;<?= e($item['custom_text']) ?>&rdquo;</span>
                                <?php endif; ?>
                                <?php if ($item['custom_image_path']): ?>
                                    <span class="custom-tag">
                                        <img src="<?= upload_url('customizations', $item['custom_image_path']) ?>"
                                             alt="Your uploaded photo">
                                        Your photo
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted" style="font-size:.85rem;margin:0">Ready-made design</p>
                        <?php endif; ?>

                        <?php if ($overStock): ?>
                            <p class="stock-line stock-out" style="margin:0">
                                Only <?= (int)$item['stock_quantity'] ?> left &mdash; reduce the quantity to check out
                            </p>
                        <?php endif; ?>

                        <div class="cart-item-controls">
                            <form method="post" action="<?= BASE_URL ?>/customer/update_cart.php"
                                  class="d-flex align-items-center gap-2" data-autosubmit>
                                <?= csrf_field() ?>
                                <input type="hidden" name="cart_item_id" value="<?= $itemId ?>">
                                <label class="visually-hidden" for="qty<?= $itemId ?>">
                                    Quantity for <?= e($item['product_name']) ?>
                                </label>
                                <div class="qty-stepper qty-stepper-sm">
                                    <button type="button" data-step="-1" aria-label="Decrease quantity"><?= icon('minus', 14) ?></button>
                                    <input type="number" id="qty<?= $itemId ?>" name="quantity"
                                           value="<?= (int)$item['quantity'] ?>" min="1" max="<?= $maxStock ?>"
                                           data-max-stock="<?= $maxStock ?>">
                                    <button type="button" data-step="1" aria-label="Increase quantity"><?= icon('plus', 14) ?></button>
                                </div>
                                <noscript><button type="submit" class="btn btn-secondary btn-sm">Update</button></noscript>
                            </form>

                            <form method="post" action="<?= BASE_URL ?>/customer/remove_cart_item.php"
                                  data-confirm
                                  data-confirm-title="Remove from cart?"
                                  data-confirm-body="Remove &quot;<?= e($item['product_name']) ?>&quot; from your cart?"
                                  data-confirm-note="You can add it again at any time."
                                  data-confirm-action="Remove">
                                <?= csrf_field() ?>
                                <input type="hidden" name="cart_item_id" value="<?= $itemId ?>">
                                <button type="submit" class="link-remove">
                                    <?= icon('trash', 14) ?> Remove
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="cart-item-side">
                        <span class="cart-item-total mono-num" data-line-total
                              data-unit-price="<?= (float)$item['price'] ?>"><?= format_price($lineTotal) ?></span>
                        <span class="cart-item-unit mono-num"><?= format_price($item['price']) ?> each</span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- ---------------------------- summary ---------------------------- -->
        <aside class="summary-card" aria-label="Order summary">
            <h2>Order summary</h2>
            <div class="summary-body">
                <div class="summary-row">
                    <span>Subtotal (<span data-cart-count><?= $unitCount ?>
                        <?= $unitCount === 1 ? 'item' : 'items' ?></span>)</span>
                    <span class="val mono-num" data-cart-subtotal><?= format_price($subtotal) ?></span>
                </div>
                <div class="summary-row muted">
                    <span>Shipping</span>
                    <span class="val">Free</span>
                </div>
                <div class="summary-total">
                    <span class="lbl">Total</span>
                    <span class="amt mono-num" data-cart-total><?= format_price($subtotal) ?></span>
                </div>
            </div>
            <div class="summary-foot">
                <a href="<?= BASE_URL ?>/customer/checkout.php" class="btn btn-primary btn-lg btn-block">
                    Proceed to checkout <?= icon('arrow-right', 18) ?>
                </a>
                <p class="summary-note">
                    <?= icon('wallet', 15) ?>
                    <span>Pay cash on delivery or by bank transfer. Nothing is charged online.</span>
                </p>
            </div>
        </aside>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/customer/footer.php'; ?>
