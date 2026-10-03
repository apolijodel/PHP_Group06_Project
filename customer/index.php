<?php
require_once __DIR__ . '/../includes/customer/product_query.php';

$categories = categories_with_counts();

// The Shop section carousel. It pages through these in threes, so it wants
// more than a handful — but not the whole catalogue on a large store, which
// is what "View all products" and shop.php are for.
$shopProducts = fetch_products(product_filters([]), 12);

$priceBounds = db()->query('SELECT MIN(price) AS lo, MAX(price) AS hi FROM products')->fetch()
    ?: ['lo' => null, 'hi' => null];

$productCount = (int)db()->query('SELECT COUNT(*) FROM products')->fetchColumn();
// Counts the storefront advertises, so they match what the studio offers.
$shapeCount = (int)db()->query("SELECT COUNT(*) FROM shapes WHERE status = 'approved'")->fetchColumn();
$designCount = (int)db()->query("SELECT COUNT(*) FROM designs WHERE status = 'approved'")->fetchColumn();

$contactError = flash_get('contact_error');
$contactSuccess = flash_get('contact_success');
$contactOld = flash_get('contact_old') ?? ['name' => '', 'email' => '', 'message' => ''];

$pageTitle = 'Handmade Personalized Bookmarks';
$fullWidth = true;
require __DIR__ . '/../includes/customer/header.php';
?>

<!-- ============================ Hero ============================ -->
<section class="hero" id="top">
    <div class="container">
        <div class="hero-grid">
            <div class="hero-copy">
                <p class="eyebrow">Handmade &middot; Personalized &middot; Yours</p>
                <h1>A bookmark as unique as your story.</h1>
                <p class="lede">Choose a shape and a design, upload a photo you love, and add a name or a
                    line worth remembering. We craft it by hand and send it your way.</p>

                <div class="hero-actions">
                    <a href="#shop" class="btn btn-primary btn-lg">
                        Shop Bookmarks <?= icon('arrow-right', 18) ?>
                    </a>
                    <a href="#how-it-works" class="btn btn-secondary btn-lg">How It Works</a>
                </div>

                <div class="hero-flow">
                    <span class="step"><?= icon('wand', 15) ?> Customize</span>
                    <span class="arrow" aria-hidden="true"><?= icon('chevron-right', 14) ?></span>
                    <span class="step"><?= icon('eye', 15) ?> Preview</span>
                    <span class="arrow" aria-hidden="true"><?= icon('chevron-right', 14) ?></span>
                    <span class="step"><?= icon('package', 15) ?> Order</span>
                </div>
            </div>

            <div class="hero-stage" aria-hidden="true">
                <div class="bookmark bookmark-a">For Mama</div>
                <div class="bookmark bookmark-b">Keep&nbsp;going.</div>
                <div class="bookmark bookmark-c">Ch. 12</div>
                <span class="hero-chip hero-chip-1"><?= icon('image', 15) ?> Your photo</span>
                <span class="hero-chip hero-chip-2"><?= icon('type', 15) ?> Your words</span>
            </div>
        </div>
    </div>
</section>

<!-- ======================== Value strip ========================= -->
<div class="container">
    <div class="value-strip">
        <div class="value-item">
            <?= icon('scissors', 20) ?>
            <div><h3>Made by hand</h3><p>Cut, printed and finished one at a time.</p></div>
        </div>
        <div class="value-item">
            <?= icon('eye', 20) ?>
            <div><h3>Preview before you buy</h3><p>See your bookmark before it is made.</p></div>
        </div>
        <div class="value-item">
            <?= icon('wallet', 20) ?>
            <div><h3>Pay on delivery</h3><p>Cash on delivery or manual bank transfer.</p></div>
        </div>
        <div class="value-item">
            <?= icon('truck', 20) ?>
            <div><h3>Track your order</h3><p>Follow every step from packing to doorstep.</p></div>
        </div>
    </div>
</div>

<!-- ========================== Our Story ========================= -->
<section class="section" id="story">
    <div class="container" data-reveal>
        <div class="story-grid">
            <div class="story-media" aria-hidden="true">
                <div class="bookmark-row">
                    <div class="bookmark">Ate Rina</div>
                    <div class="bookmark">Ch. 1</div>
                    <div class="bookmark">2019</div>
                </div>
            </div>
            <div class="story-copy">
                <p class="eyebrow">Our Story</p>
                <h2>Made for readers, gift-givers, and everyone in between.</h2>
                <p class="story-quote">&ldquo;Every book someone loves deserves a bookmark that means
                    something too.&rdquo;</p>
                <p class="text-soft">MarkMe started as a batch of handmade class gifts. It grew into a small
                    studio where students, teachers and stationery lovers design a bookmark that is genuinely
                    theirs &mdash; a name, a favourite line, a photo worth keeping, on a shape and design
                    they picked themselves.</p>
                <div class="story-stats">
                    <div><span class="num mono-num"><?= $shapeCount ?></span><span class="lbl">Shapes</span></div>
                    <div><span class="num mono-num"><?= $designCount ?></span><span class="lbl">Designs</span></div>
                    <div><span class="num mono-num"><?= $productCount ?></span><span class="lbl">Products</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ======================== How It Works ======================== -->
<section class="section section-cream" id="how-it-works">
    <div class="container" data-reveal>
        <div class="section-head section-head-center">
            <p class="eyebrow">How It Works</p>
            <h2>Make it yours in 3 simple steps</h2>
        </div>

        <div class="steps-grid">
            <article class="step-card">
                <div class="step-top">
                    <span class="step-num">01</span>
                    <span class="step-icon"><?= icon('wand', 22) ?></span>
                </div>
                <h3>Personalize</h3>
                <p>Pick your shape, design, photo and text.</p>
            </article>

            <article class="step-card">
                <div class="step-top">
                    <span class="step-num">02</span>
                    <span class="step-icon"><?= icon('eye', 22) ?></span>
                </div>
                <h3>Preview</h3>
                <p>See your bookmark before you order.</p>
            </article>

            <article class="step-card">
                <div class="step-top">
                    <span class="step-num">03</span>
                    <span class="step-icon"><?= icon('package', 22) ?></span>
                </div>
                <h3>Order</h3>
                <p>Check out and we&rsquo;ll craft it by hand.</p>
            </article>
        </div>
    </div>
</section>

<!-- ============================ Shop =========================== -->
<!-- The shop lives here, in the same page and the same visual language as
     every other section. Category chips filter the carousel in place via
     shop_products.php; shop.php is the full grid and the no-JavaScript
     fallback, reached from "View all products". -->
<section class="section" id="shop">
    <div class="container" data-reveal>
        <!-- Heading centred in the section, with the link parked on the right.
             The three-column grid keeps the heading optically centred rather
             than pushed off-centre by the button's width. -->
        <div class="section-head-split">
            <div class="shs-spacer" aria-hidden="true"></div>
            <div class="shs-title">
                <p class="eyebrow">Shop</p>
                <h2>Bookmarks worth keeping</h2>
            </div>
            <div class="shs-action">
                <a href="<?= BASE_URL ?>/customer/shop.php" class="btn btn-secondary btn-sm">
                    View all products <?= icon('arrow-right', 15) ?>
                </a>
            </div>
        </div>

        <!-- Buttons, not links: choosing a category filters the carousel
             below rather than navigating to a category page. -->
        <div class="filter-pills filter-pills-center" role="group" aria-label="Filter by category">
            <button type="button" class="pill active" data-filter-category="" aria-pressed="true">All</button>
            <?php foreach ($categories as $cat): ?>
                <button type="button" class="pill" aria-pressed="false"
                        data-filter-category="<?= (int)$cat['category_id'] ?>">
                    <?= e($cat['name']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <p class="shop-count" data-shop-status aria-live="polite">
            <?= count($shopProducts) ?> <?= count($shopProducts) === 1 ? 'bookmark' : 'bookmarks' ?><?php
                if ($priceBounds['lo'] !== null): ?>, from <?= format_price((float)$priceBounds['lo']) ?><?php
                endif; ?>
        </p>

        <div class="carousel">
            <button type="button" class="carousel-nav prev" data-carousel-prev aria-label="Previous products">
                <?= icon('chevron-left', 20) ?>
            </button>

            <div class="carousel-viewport">
                <div class="carousel-track product-grid-carousel" data-carousel-track>
                    <?php if (!$shopProducts): ?>
                        <div class="shop-empty">
                            <span class="empty-icon"><?= icon('package', 26) ?></span>
                            <h3>No products yet</h3>
                            <p>New bookmarks are on their way. Check back soon.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($shopProducts as $product): require __DIR__ . '/_product_card.php'; endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <button type="button" class="carousel-nav next" data-carousel-next aria-label="More products">
                <?= icon('chevron-right', 20) ?>
            </button>
        </div>

        <noscript>
            <p class="shop-count" style="margin-top:var(--s-5)">
                <a href="<?= BASE_URL ?>/customer/shop.php">Browse all bookmarks and filter by category &rarr;</a>
            </p>
        </noscript>
    </div>
</section>

<!-- ============================= FAQ ============================ -->
<!-- A section of its own, after the shop: browse first, then the
     questions, then get in touch. The tinted background separates it
     from the plain Shop band above and the Contact band below. -->
<section class="section section-cream" id="faq">
    <div class="container" data-reveal>
        <div class="section-head section-head-center">
            <p class="eyebrow">FAQ</p>
            <h2>Questions? We&rsquo;ve got you.</h2>
        </div>

        <!-- Native <details> — an accordion with no extra JavaScript. -->
        <div class="faq-list">
            <details class="faq-item">
                <summary>Can I customize my bookmark?<span class="faq-mark" aria-hidden="true"><?= icon('chevron-down', 18) ?></span></summary>
                <p>Yes &mdash; pick a shape, a design, a photo and your own text. You&rsquo;ll need an account,
                    and you can preview it before ordering.</p>
            </details>

            <details class="faq-item">
                <summary>Can I upload my own photo?<span class="faq-mark" aria-hidden="true"><?= icon('chevron-down', 18) ?></span></summary>
                <p>You can. JPG, PNG or WEBP, up to 2MB.</p>
            </details>

            <details class="faq-item">
                <summary>Can I save my design?<span class="faq-mark" aria-hidden="true"><?= icon('chevron-down', 18) ?></span></summary>
                <p>Press <strong>Save design</strong> in the studio. It&rsquo;s kept in
                    <a href="<?= BASE_URL ?>/customer/my_designs.php">Saved Designs</a> to edit, reorder or delete.</p>
            </details>

            <details class="faq-item">
                <summary>How do I track my order?<span class="faq-mark" aria-hidden="true"><?= icon('chevron-down', 18) ?></span></summary>
                <p>Open <a href="<?= BASE_URL ?>/customer/orders.php">My Orders</a>. Each one moves from
                    Pending to On Shipping to Completed.</p>
            </details>

            <details class="faq-item">
                <summary>What payment methods are available?<span class="faq-mark" aria-hidden="true"><?= icon('chevron-down', 18) ?></span></summary>
                <p>Cash on delivery, or manual bank transfer. Nothing is charged online.</p>
            </details>

            <details class="faq-item">
                <summary>Can I cancel my order?<span class="faq-mark" aria-hidden="true"><?= icon('chevron-down', 18) ?></span></summary>
                <p>Message us while it&rsquo;s still Pending and we&rsquo;ll cancel it for you. Once it ships
                    we can no longer stop it.</p>
            </details>
        </div>
    </div>
</section>

<!-- =========================== Contact ========================== -->
<section class="section" id="contact">
    <div class="container" data-reveal>
        <div class="contact-grid">
            <div>
                <p class="eyebrow">Get in touch</p>
                <h2 style="margin-top:var(--s-3)">Contact us</h2>
                <p class="text-soft" style="margin-top:var(--s-3)">Questions about an order, a custom
                    request, or just feedback? We read every message.</p>
                <ul class="contact-points">
                    <li><?= icon('mail', 19) ?><div><strong>hello@markme.test</strong><span>We reply within one working day</span></div></li>
                    <li><?= icon('clock', 19) ?><div><strong>Mon &ndash; Sat, 9am &ndash; 6pm</strong><span>Philippine Standard Time</span></div></li>
                    <li><?= icon('truck', 19) ?><div><strong>Nationwide delivery</strong><span>Cash on delivery available</span></div></li>
                </ul>
            </div>

            <div class="card card-pad">
                <?php if ($contactSuccess): ?>
                    <div class="alert alert-success d-flex gap-2 align-items-start" role="status">
                        <?= icon('check-circle', 20) ?><span><?= e($contactSuccess) ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($contactError): ?>
                    <div class="alert alert-danger d-flex gap-2 align-items-start" role="alert">
                        <?= icon('alert', 20) ?><span><?= e($contactError) ?></span>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= BASE_URL ?>/customer/contact_submit.php">
                    <?= csrf_field() ?>
                    <div class="form-grid">
                        <div>
                            <label class="form-label" for="contact_name">Name</label>
                            <input type="text" id="contact_name" name="name" class="form-control"
                                   value="<?= e($contactOld['name']) ?>" required>
                        </div>
                        <div>
                            <label class="form-label" for="contact_email">Email</label>
                            <input type="email" id="contact_email" name="email" class="form-control"
                                   value="<?= e($contactOld['email']) ?>" required>
                        </div>
                        <div class="span-2">
                            <label class="form-label" for="contact_message">Message</label>
                            <textarea id="contact_message" name="message" class="form-control" rows="7"
                                      required><?= e($contactOld['message']) ?></textarea>
                        </div>
                    </div>
                    <!-- Sized by its own padding, not the form width. -->
                    <button type="submit" class="btn btn-primary" style="margin-top:var(--s-5)">
                        <?= icon('mail', 16) ?> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/customer/footer.php'; ?>
