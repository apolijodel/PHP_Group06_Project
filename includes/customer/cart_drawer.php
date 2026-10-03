<?php
/**
 * Right-side cart drawer. Included from the footer for signed-in customers.
 *
 * The shell is rendered once per page; its contents are (re)loaded from
 * customer/cart_partial.php whenever the cart changes, so adding an item never
 * navigates away from what the customer was looking at.
 *
 * cart.php remains the full page — this is a shortcut, not a replacement, and
 * every mutation still goes through the same guarded POST endpoints.
 */
?>
<div class="drawer-scrim" id="cartScrim" hidden></div>

<aside class="cart-drawer" id="cartDrawer" aria-label="Your cart" aria-hidden="true" hidden>
    <header class="cart-drawer-head">
        <h2><?= icon('cart', 20) ?> Your Cart</h2>
        <button type="button" class="btn-icon" data-cart-close aria-label="Close cart">
            <?= icon('close', 20) ?>
        </button>
    </header>

    <!-- Replaced wholesale by the partial; the spinner is what shows first. -->
    <div class="cart-drawer-body" id="cartDrawerBody" aria-live="polite" aria-busy="true">
        <p class="cart-drawer-loading">Loading your cart&hellip;</p>
    </div>
</aside>
