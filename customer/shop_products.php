<?php
/**
 * AJAX partial: the product cards for a given filter set.
 *
 * The category chips in the Shop section fetch this and swap the carousel's
 * contents, so choosing a category never navigates away. It runs the same
 * fetch_products() query as the no-JavaScript Shop page, so the two can
 * never disagree — this is a rendering shortcut, not a second code path.
 *
 * Returns an HTML fragment (the cards only). Guests may call it: the shop is
 * public, and every action *on* a card is guarded separately.
 */
require_once __DIR__ . '/../includes/customer/product_query.php';
require_once __DIR__ . '/../includes/icons.php';

header('Content-Type: text/html; charset=utf-8');
// A filtered listing is per-request and must not be stored by the browser.
header('Cache-Control: no-store');

$filters = product_filters($_GET);
$products = fetch_products($filters);

if (!$products) {
    ?>
    <div class="shop-empty">
        <span class="empty-icon"><?= icon('search', 26) ?></span>
        <h3>Nothing here yet</h3>
        <p>No bookmarks match this filter. Try another category.</p>
    </div>
    <?php
    exit;
}

foreach ($products as $product) {
    require __DIR__ . '/_product_card.php';
}
