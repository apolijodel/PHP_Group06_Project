</main>
<footer class="site-footer">
    <div class="container">
        <div class="site-footer-top">
            <div>
                <a class="brand-mark" href="<?= BASE_URL ?>/customer/index.php">MarkMe</a>
                <p style="margin-top:var(--s-4);max-width:34ch">Handmade, personalized bookmarks. Pick a shape
                    and a design, add your photo and your words, and we'll craft it just for you.</p>
            </div>

            <div>
                <h2>Explore</h2>
                <ul>
                    <li><a href="<?= BASE_URL ?>/customer/index.php">Home</a></li>
                    <li><a href="<?= BASE_URL ?>/customer/index.php#story">Our Story</a></li>
                    <li><a href="<?= BASE_URL ?>/customer/index.php#how-it-works">How It Works</a></li>
                    <li><a href="<?= BASE_URL ?>/customer/shop.php">Shop</a></li>
                </ul>
            </div>

            <div>
                <h2>Support</h2>
                <ul>
                    <li><a href="<?= BASE_URL ?>/customer/index.php#faq">FAQ</a></li>
                    <li><a href="<?= BASE_URL ?>/customer/index.php#contact">Contact Us</a></li>
                    <li><a href="<?= BASE_URL ?>/customer/settings.php">Settings</a></li>
                    <li><a href="<?= BASE_URL ?>/customer/privacy.php">Privacy Policy</a></li>
                </ul>
            </div>

            <div>
                <h2>Payment</h2>
                <p style="margin-bottom:var(--s-4)">Pay on delivery, or by manual bank transfer. No online
                    payment gateway needed.</p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="pay-chip"><?= icon('wallet', 15) ?> Cash on Delivery</span>
                    <span class="pay-chip"><?= icon('bank', 15) ?> Bank Transfer</span>
                </div>
            </div>
        </div>

        <div class="site-footer-bottom">
            <span>&copy; <?= date('Y') ?> MarkMe — Customizable Bookmark E-Commerce System</span>
            <span><a href="<?= BASE_URL ?>/customer/privacy.php">Privacy Policy</a>
                &middot; Made by hand in the Philippines</span>
        </div>
    </div>
</footer>
<?php require __DIR__ . '/../confirm_dialog.php'; ?>

<!-- Cookie notice. MarkMe sets exactly one cookie - the session - so this
     says that plainly rather than offering switches for tracking that does
     not exist. Hidden until the script confirms no choice has been stored,
     so it never flashes up for someone who already answered. -->
<div class="cookie-banner" data-cookie-banner hidden role="region" aria-label="Cookie notice">
    <p>We use one cookie to keep you signed in. No tracking, no advertising.
        <a href="<?= BASE_URL ?>/customer/privacy.php#cookies">Read the privacy policy</a>.</p>
    <div class="cookie-actions">
        <button type="button" class="btn btn-secondary btn-sm" data-cookie-choice="declined">Decline</button>
        <button type="button" class="btn btn-primary btn-sm" data-cookie-choice="accepted">Accept</button>
    </div>
</div>

<!-- Toast region: brief confirmations ("Added to cart") without an alert()
     and without navigating. Polite, so it never interrupts a screen reader. -->
<div class="toast-region" id="toastRegion" role="status" aria-live="polite" aria-atomic="false"></div>

<?php if (is_logged_in()): require __DIR__ . '/cart_drawer.php'; endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!is_logged_in()): require __DIR__ . '/auth_modals.php'; endif; ?>
<script src="<?= BASE_URL ?>/assets/js/script.js?v=<?= filemtime(__DIR__ . '/../../assets/js/script.js') ?>"></script>
</body>
</html>
