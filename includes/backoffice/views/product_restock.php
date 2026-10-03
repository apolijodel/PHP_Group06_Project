<?php
/**
 * Screen: product_restock - markup.
 *
 * Rendered inside whichever panel shell required it - the administrator
 * panel and the staff panel share this one copy.
 *
 * Auth and form handling happen in includes/backoffice/screens/product_restock.php, before any
 * output. Read-only queries that were already here stayed here rather
 * than being moved for the sake of it.
 */
?>
<?php 
$stock = (int)$product['stock_quantity'];
?>

<nav class="breadcrumbs" aria-label="Breadcrumb">
    <a href="<?= panel_url('products.php') ?>">Products</a>
    <span class="sep" aria-hidden="true"><?= icon('chevron-right', 13) ?></span>
    <span aria-current="page">Restock</span>
</nav>

<div class="page-head">
    <div>
        <p class="eyebrow eyebrow-muted">Inventory</p>
        <h2 style="margin-top:var(--s-2)">Restock <?= e($product['name']) ?></h2>
        <p>Adds to the current stock rather than replacing it.</p>
    </div>
</div>

<div class="cart-grid">
    <div class="card card-flush">
        <div class="panel-head"><h2>Add stock</h2></div>
        <div class="panel-body">
            <div class="cart-item" style="grid-template-columns:92px minmax(0,1fr);padding:0 0 var(--s-5);border-bottom:1px solid var(--border)">
                <div class="cart-item-media">
                    <img src="<?= upload_url('products', $product['image_path']) ?>"
                         alt="<?= e($product['name']) ?>" loading="lazy" decoding="async">
                </div>
                <div class="cart-item-main">
                    <h3 class="cart-item-name"><?= e($product['name']) ?></h3>
                    <p class="cart-item-unit mono-num" style="margin:0"><?= format_price($product['price']) ?></p>
                    <p class="stock-line stock-<?= stock_state($stock)[0] ?>" style="margin:0">
                        Current stock: <?= $stock ?>
                    </p>
                </div>
            </div>

            <form method="post" style="margin-top:var(--s-5)">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
                <div class="field-group" style="max-width:260px">
                    <label class="form-label" for="add_quantity">Quantity to add</label>
                    <div class="qty-stepper">
                        <button type="button" data-step="-1" aria-label="Decrease"><?= icon('minus', 16) ?></button>
                        <input type="number" id="add_quantity" name="add_quantity" value="10" min="1" max="9999" required>
                        <button type="button" data-step="1" aria-label="Increase"><?= icon('plus', 16) ?></button>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary"><?= icon('plus', 16) ?> Add stock</button>
                    <a href="<?= panel_url('products.php') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <aside class="summary-card">
        <h2>Stock health</h2>
        <div class="summary-body">
            <div class="summary-row">
                <span>Current stock</span>
                <span class="val mono-num"><?= $stock ?></span>
            </div>
            <div class="summary-row">
                <span>Low-stock threshold</span>
                <span class="val mono-num">5</span>
            </div>
            <p class="summary-note" style="margin-top:var(--s-2)">
                <?= icon('info', 15) ?>
                <span>Customers can&rsquo;t order more than the stock on hand, and an order reduces it
                    automatically.</span>
            </p>
        </div>
    </aside>
</div>
