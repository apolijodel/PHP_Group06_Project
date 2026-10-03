<?php
/**
 * The contents of the cart drawer: line items, subtotal and the two actions.
 *
 * Read-only — every change still posts to update_cart.php / remove_cart_item.php,
 * which own the validation. This just renders whatever cart_contents() reports,
 * so the drawer and the cart page can never show different numbers.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/icons.php';
require_login();

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

$cart = cart_contents();
$items = $cart['items'];
?>

<?php if (!$items): ?>
    <div class="cart-drawer-empty">
        <span class="empty-icon"><?= icon('cart', 26) ?></span>
        <h3>Your cart is empty</h3>
        <p>Find a bookmark worth keeping and it will show up here.</p>
        <a href="<?= BASE_URL ?>/customer/index.php#shop" class="btn btn-primary btn-sm" data-cart-close>
            Browse bookmarks
        </a>
    </div>
<?php else: ?>
    <ul class="cart-drawer-list">
        <?php foreach ($items as $item): ?>
            <?php
            $itemId = (int)$item['cart_item_id'];
            $maxStock = max(1, (int)$item['stock_quantity']);
            $overStock = (int)$item['quantity'] > (int)$item['stock_quantity'];
            $custom = customization_summary($item);
            ?>
            <li class="cart-drawer-item" data-cart-row>
                <a class="cdi-media" href="<?= BASE_URL ?>/customer/product.php?id=<?= (int)$item['product_id'] ?>">
                    <img src="<?= e(upload_url('products', $item['product_image'])) ?>"
                         alt="<?= e($item['product_name']) ?>" width="64" height="64" loading="lazy">
                </a>

                <div class="cdi-main">
                    <a class="cdi-name" href="<?= BASE_URL ?>/customer/product.php?id=<?= (int)$item['product_id'] ?>">
                        <?= e($item['product_name']) ?>
                    </a>
                    <?php if ($custom !== ''): ?>
                        <p class="cdi-custom"><?= e($custom) ?></p>
                    <?php endif; ?>
                    <p class="cdi-price mono-num"><?= format_price($item['price']) ?> each</p>

                    <?php if ($overStock): ?>
                        <p class="stock-line stock-out" style="margin:0;font-size:.78rem">
                            Only <?= (int)$item['stock_quantity'] ?> left
                        </p>
                    <?php endif; ?>

                    <div class="cdi-controls">
                        <form method="post" action="<?= BASE_URL ?>/customer/update_cart.php" data-cart-qty>
                            <?= csrf_field() ?>
                            <input type="hidden" name="cart_item_id" value="<?= $itemId ?>">
                            <label class="visually-hidden" for="dq<?= $itemId ?>">
                                Quantity for <?= e($item['product_name']) ?>
                            </label>
                            <div class="qty-stepper qty-stepper-sm">
                                <button type="button" data-step="-1" aria-label="Decrease quantity"><?= icon('minus', 14) ?></button>
                                <input type="number" id="dq<?= $itemId ?>" name="quantity"
                                       value="<?= (int)$item['quantity'] ?>" min="1" max="<?= $maxStock ?>"
                                       data-max-stock="<?= $maxStock ?>">
                                <button type="button" data-step="1" aria-label="Increase quantity"><?= icon('plus', 14) ?></button>
                            </div>
                            <noscript><button type="submit" class="btn btn-secondary btn-sm">Update</button></noscript>
                        </form>

                        <form method="post" action="<?= BASE_URL ?>/customer/remove_cart_item.php" data-cart-remove>
                            <?= csrf_field() ?>
                            <input type="hidden" name="cart_item_id" value="<?= $itemId ?>">
                            <button type="submit" class="link-remove"
                                    aria-label="Remove <?= e($item['product_name']) ?> from cart">
                                <?= icon('trash', 13) ?> Remove
                            </button>
                        </form>
                    </div>
                </div>

                <span class="cdi-total mono-num" data-line-total
                      data-unit-price="<?= (float)$item['price'] ?>"><?= format_price($item['price'] * $item['quantity']) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>

    <footer class="cart-drawer-foot">
        <div class="summary-row">
            <span>Subtotal (<span data-cart-count><?= (int)$cart['count'] ?>
                <?= (int)$cart['count'] === 1 ? 'item' : 'items' ?></span>)</span>
            <span class="val mono-num" data-cart-subtotal><?= format_price($cart['subtotal']) ?></span>
        </div>
        <div class="summary-row muted">
            <span>Shipping</span>
            <span class="val">Free</span>
        </div>
        <div class="summary-total">
            <span class="lbl">Total</span>
            <span class="amt mono-num" data-cart-total><?= format_price($cart['subtotal']) ?></span>
        </div>

        <div class="cart-drawer-actions">
            <a href="<?= BASE_URL ?>/customer/cart.php" class="btn btn-secondary btn-block">View Cart</a>
            <a href="<?= BASE_URL ?>/customer/checkout.php" class="btn btn-primary btn-block">
                Checkout <?= icon('arrow-right', 16) ?>
            </a>
        </div>
    </footer>
<?php endif; ?>
