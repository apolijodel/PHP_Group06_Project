<?php
/**
 * Shared product card — the one product tile used by the home Shop section,
 * the Shop page, the AJAX filter endpoint and the related-products strip, so
 * they can never drift apart.
 *
 * Expects $product (a products row joined with category_name).
 */
if (!isset($product) || !is_array($product)) {
    return;
}

$pid = (int)$product['product_id'];
$stock = (int)$product['stock_quantity'];
$threshold = (int)($product['low_stock_threshold'] ?? 5);
[$stockClass, $stockLabel] = stock_state($stock, $threshold);
$isCustom = (bool)$product['is_customizable'];
$url = BASE_URL . '/customer/product.php?id=' . $pid;
$signedIn = is_logged_in();
?>
<article class="product-card<?= $stock <= 0 ? ' is-out' : '' ?>">
    <a class="product-media" href="<?= $url ?>" tabindex="-1" aria-hidden="true">
        <img src="<?= e(upload_url('products', $product['image_path'])) ?>"
             alt="" loading="lazy" width="600" height="450">
        <span class="product-flags">
            <span class="badge badge-cat"><?= e($product['category_name']) ?></span>
            <?php if ($isCustom): ?>
                <span class="badge badge-ok" title="Personalizable"><?= icon('wand', 13, '', 'Personalizable') ?></span>
            <?php endif; ?>
        </span>
    </a>

    <div class="product-body">
        <h3 class="product-name"><a href="<?= $url ?>"><?= e($product['name']) ?></a></h3>
        <?php if (!empty($product['description'])): ?>
            <p class="product-desc"><?= e($product['description']) ?></p>
        <?php endif; ?>
        <div class="product-meta">
            <span class="product-price mono-num"><?= format_price($product['price']) ?></span>
            <span class="stock-line stock-<?= $stockClass ?>"><?= e($stockLabel) ?></span>
        </div>
    </div>

    <!-- One primary action per card; the image and title already link to the
         product page, so a second "view" button would only add noise. -->
    <div class="product-actions">
        <?php if ($stock <= 0): ?>
            <a class="btn btn-secondary" href="<?= $url ?>">View details</a>

        <?php elseif (!$signedIn): ?>
            <!-- Customizing and adding to cart both need an account. Rather than
                 letting the click fail at the server, guests are shown the
                 login prompt here. add_to_cart.php still enforces this. -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#loginRequiredModal"
                    data-login-reason="<?= $isCustom ? 'customize' : 'cart' ?>">
                <?= $isCustom ? icon('wand', 15) . ' Customize' : icon('cart', 15) . ' Add to cart' ?>
            </button>

        <?php elseif ($isCustom): ?>
            <a class="btn btn-primary" href="<?= $url ?>#studio">
                <?= icon('wand', 15) ?> Customize
            </a>

        <?php else: ?>
            <form method="post" action="<?= BASE_URL ?>/customer/add_to_cart.php" data-cart-add>
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= $pid ?>">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-primary">
                    <?= icon('cart', 15) ?> Add to cart
                </button>
            </form>
        <?php endif; ?>
    </div>
</article>
