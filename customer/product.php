<?php
require_once __DIR__ . '/../includes/functions.php';

$productId = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare(
    'SELECT p.*, c.name AS category_name FROM products p
     JOIN categories c ON c.category_id = p.category_id
     WHERE p.product_id = :id'
);
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Product not found';
    require __DIR__ . '/../includes/customer/header.php';
    ?>
    <div class="card empty-state">
        <span class="empty-icon"><?= icon('search', 26) ?></span>
        <h1>We couldn&rsquo;t find that product</h1>
        <p>It may have been removed, or the link might be out of date.</p>
        <a href="<?= BASE_URL ?>/customer/shop.php" class="btn btn-primary" style="margin-top:var(--s-4)">
            Browse the shop
        </a>
    </div>
    <?php
    require __DIR__ . '/../includes/customer/footer.php';
    exit;
}

$isCustom = (bool)$product['is_customizable'];
/* Only approved options reach a customer. A shape or design that is waiting
   for review, or that was rejected, is not something anyone can choose. */
$shapes = $isCustom
    ? db()->query("SELECT * FROM shapes WHERE status = 'approved' ORDER BY name")->fetchAll()
    : [];
$designs = $isCustom
    ? db()->query("SELECT * FROM designs WHERE status = 'approved' ORDER BY name")->fetchAll()
    : [];

$stock = (int)$product['stock_quantity'];
[$stockClass, $stockLabel] = stock_state($stock, (int)($product['low_stock_threshold'] ?? 5));
$outOfStock = $stock <= 0;

$relatedStmt = db()->prepare(
    'SELECT p.*, c.name AS category_name FROM products p
     JOIN categories c ON c.category_id = p.category_id
     WHERE p.category_id = :cat AND p.product_id != :id
     ORDER BY (p.stock_quantity > 0) DESC, p.created_at DESC LIMIT 4'
);
$relatedStmt->execute(['cat' => $product['category_id'], 'id' => $productId]);
$related = $relatedStmt->fetchAll();

// Preview tints: help a customer picture the bookmark. Not an order option —
// cart_items has no colour column, so nothing here is submitted or promised.
$tints = [
    ['#2f6f5e', 'Forest'],
    ['#bd5518', 'Terracotta'],
    ['#d9a441', 'Gold'],
    ['#1f1d1a', 'Charcoal'],
    ['#8a6f9e', 'Lilac'],
];

// Routed through upload_url() like every other /uploads reference: it
// rawurlencodes the filename and appends a filemtime cache-buster, so replacing
// artwork under an existing filename actually shows up.
$mainImage = upload_url('products', $product['image_path']);

/**
 * When the studio is opened from Saved Designs (?saved=N) the form is prefilled
 * from that row and saving updates it instead of creating a duplicate. The row
 * is scoped to the session user, so another customer's id simply finds nothing.
 */
$savedDesign = null;
$savedDesignId = (int)($_GET['saved'] ?? 0);

if ($isCustom && $savedDesignId > 0 && is_logged_in()) {
    $stmt = db()->prepare(
        'SELECT * FROM saved_designs
          WHERE saved_design_id = :id AND user_id = :uid AND product_id = :pid'
    );
    $stmt->execute([
        'id' => $savedDesignId,
        'uid' => current_user()['user_id'],
        'pid' => $productId,
    ]);
    $savedDesign = $stmt->fetch() ?: null;
}

/** True when this option should start selected. */
$isChosen = static function (?int $savedValue, int $optionId, int $index) use ($savedDesign): bool {
    return $savedDesign ? $savedValue === $optionId : $index === 0;
};

$pageTitle = $product['name'];
$fullWidth = true;
require __DIR__ . '/../includes/customer/header.php';
?>

<div class="container page-shell">
<nav class="breadcrumbs" aria-label="Breadcrumb">
    <a href="<?= BASE_URL ?>/customer/index.php">Home</a>
    <span class="sep" aria-hidden="true"><?= icon('chevron-right', 13) ?></span>
    <a href="<?= BASE_URL ?>/customer/shop.php">Shop</a>
    <span class="sep" aria-hidden="true"><?= icon('chevron-right', 13) ?></span>
    <a href="<?= BASE_URL ?>/customer/shop.php?category=<?= (int)$product['category_id'] ?>"><?= e($product['category_name']) ?></a>
    <span class="sep" aria-hidden="true"><?= icon('chevron-right', 13) ?></span>
    <span aria-current="page"><?= e($product['name']) ?></span>
</nav>

<div class="pdp-grid">
    <!-- ------------------------------ gallery ------------------------------ -->
    <div class="pdp-gallery" data-gallery>
        <div class="pdp-main-img">
            <img src="<?= $mainImage ?>" alt="<?= e($product['name']) ?>" data-gallery-main
                 width="600" height="600">
        </div>

        <?php if ($designs): ?>
            <div class="pdp-thumbs" role="group" aria-label="Product and design views">
                <button type="button" class="pdp-thumb is-active" aria-pressed="true"
                        data-gallery-thumb="<?= $mainImage ?>"
                        data-gallery-alt="<?= e($product['name']) ?>"
                        title="<?= e($product['name']) ?>">
                    <img src="<?= $mainImage ?>" alt="<?= e($product['name']) ?>" loading="lazy">
                </button>
                <?php foreach ($designs as $design): ?>
                    <?php $designSrc = upload_url('designs', $design['image_path']); ?>
                    <button type="button" class="pdp-thumb" aria-pressed="false"
                            data-gallery-thumb="<?= $designSrc ?>"
                            data-gallery-alt="<?= e($design['name']) ?> design"
                            title="<?= e($design['name']) ?> design">
                        <img src="<?= $designSrc ?>" alt="<?= e($design['name']) ?> design" loading="lazy">
                    </button>
                <?php endforeach; ?>
            </div>
            <p class="form-text">Tap a swatch to see the designs you can choose from.</p>
        <?php endif; ?>
    </div>

    <!-- ------------------------------- info -------------------------------- -->
    <div class="pdp-info">
        <span class="badge badge-cat"><?= e($product['category_name']) ?></span>
        <h1><?= e($product['name']) ?></h1>

        <p class="pdp-rating">
            <span class="stars" aria-hidden="true"><?= str_repeat(icon_star_filled(15), 5) ?></span>
            <span>Handmade to order &middot; finished one at a time</span>
        </p>

        <p class="pdp-price mono-num"><?= format_price($product['price']) ?></p>

        <?php if (!empty($product['description'])): ?>
            <p class="pdp-desc"><?= nl2br(e($product['description'])) ?></p>
        <?php endif; ?>

        <p class="stock-line stock-<?= $stockClass ?>">
            <?= e($stockLabel) ?><?= $stock > 5 ? ' &mdash; ' . $stock . ' available' : '' ?>
        </p>

        <?php if ($outOfStock): ?>
            <div class="alert alert-danger d-flex gap-2 align-items-start" style="width:100%">
                <?= icon('alert', 20) ?>
                <span>This product is currently out of stock. Check back soon, or browse similar bookmarks below.</span>
            </div>
            <a href="<?= BASE_URL ?>/customer/shop.php?in_stock=1" class="btn btn-secondary">
                See what&rsquo;s in stock
            </a>

        <?php elseif ($isCustom): ?>
            <div class="pdp-buy">
                <?php if (is_logged_in()): ?>
                    <a href="#studio" class="btn btn-primary btn-lg">
                        <?= icon('wand', 18) ?> Customize this bookmark
                    </a>
                <?php else: ?>
                    <!-- Guests get the prompt rather than a studio they cannot submit. -->
                    <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal"
                            data-bs-target="#loginRequiredModal" data-login-reason="customize">
                        <?= icon('wand', 18) ?> Customize this bookmark
                    </button>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/customer/index.php#shop" class="btn btn-secondary btn-lg">Keep browsing</a>
            </div>
            <p class="form-text" style="margin-top:calc(-1 * var(--s-2))">
                <?= is_logged_in()
                    ? 'Choose your shape, design, photo and text below &mdash; then add it to your cart.'
                    : 'Personalizing needs a free account, so your designs and orders stay with you.' ?>
            </p>

        <?php else: ?>
            <form method="post" action="<?= BASE_URL ?>/customer/add_to_cart.php" class="pdp-buy" data-cart-add>
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= $productId ?>">

                <div>
                    <label class="visually-hidden" for="qtyPlain">Quantity</label>
                    <div class="qty-stepper">
                        <button type="button" data-step="-1" aria-label="Decrease quantity"><?= icon('minus', 16) ?></button>
                        <input type="number" id="qtyPlain" name="quantity" value="1" min="1"
                               max="<?= $stock ?>" data-max-stock="<?= $stock ?>">
                        <button type="button" data-step="1" aria-label="Increase quantity"><?= icon('plus', 16) ?></button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">
                    <?= icon('cart', 18) ?> Add to cart
                </button>
            </form>
        <?php endif; ?>

        <ul class="pdp-facts">
            <li><?= icon('scissors', 18) ?><span>Cut, printed and finished by hand in small batches.</span></li>
            <li><?= icon('truck', 18) ?><span>Nationwide delivery, with order tracking from your account.</span></li>
            <li><?= icon('wallet', 18) ?><span>Pay cash on delivery or by manual bank transfer.</span></li>
        </ul>
    </div>
</div>

</div><!-- /container -->

<?php if ($isCustom && !$outOfStock && !is_logged_in()): ?>
<!-- ===================== Customization: locked ======================= -->
<!-- The studio markup is not emitted at all for guests. This is the real
     boundary, not the hidden button above: add_to_cart.php and
     design_save.php also call require_login(), so a hand-typed URL or a
     replayed POST still gets nothing. -->
<section class="studio" id="studio">
    <div class="container page-shell">
        <div class="card empty-state" style="max-width:560px;margin-inline:auto">
            <span class="empty-icon"><?= icon('user', 26) ?></span>
            <h2>Login required</h2>
            <p>Please log in or create an account to customize your bookmark. Your designs and orders are
                saved to your account.</p>
            <div class="d-flex flex-wrap gap-2 justify-content-center" style="margin-top:var(--s-5)">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#loginModal">Login</button>
                <?php if (setting('allow_registration', '1') === '1'): ?>
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#registerModal">Register</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($isCustom && !$outOfStock && is_logged_in()): ?>
<!-- ======================= Customization studio ======================= -->
<section class="studio" id="studio">
    <div class="container page-shell">
        <div class="section-head" style="margin-bottom:var(--s-8)">
            <p class="eyebrow">Design Studio</p>
            <h2><?= $savedDesign ? 'Edit ' . e($savedDesign['design_name']) : 'Make it yours' ?></h2>
            <p>Work through the steps on the left. The preview on the right updates as you go.
                <?= $savedDesign
                    ? 'Saving updates this design; adding to cart orders it as shown.'
                    : 'Save it to reuse later, or add it straight to your cart.' ?></p>
        </div>

        <form method="post" action="<?= BASE_URL ?>/customer/add_to_cart.php"
              enctype="multipart/form-data" class="studio-grid" data-once data-cart-add>
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= $productId ?>">
            <?php if ($savedDesign): ?>
                <input type="hidden" name="saved_design_id"
                       value="<?= (int)$savedDesign['saved_design_id'] ?>">
            <?php endif; ?>

            <!-- ----------------------- controls ----------------------- -->
            <div class="studio-steps">
                <fieldset class="studio-card">
                    <legend class="visually-hidden">Choose your shape</legend>
                    <div class="studio-card-head">
                        <span class="studio-step-num" aria-hidden="true">1</span>
                        <h3>Choose your shape</h3>
                        <span class="hint"><?= count($shapes) ?> options</span>
                    </div>
                                        <?php /* Four at a time rather than a wall of options. Every option is
                             still rendered and still a radio in this form, so the preview
                             and the submitted value are unchanged - the carousel only
                             controls how many are on screen. */ ?>
                    <div class="carousel option-carousel" data-carousel
                         data-per-view="4 3 2" data-min-card="104"
                         data-carousel-item=".option-thumb">
                        <button type="button" class="carousel-nav prev" data-carousel-prev
                                aria-label="Previous shapes"><?= icon('chevron-left', 17) ?></button>
                        <div class="carousel-viewport">
                            <div class="carousel-track" data-carousel-track>

                        <?php foreach ($shapes as $i => $shape): ?>
                            <?php $chosen = $isChosen(
                                isset($savedDesign['shape_id']) ? (int)$savedDesign['shape_id'] : null,
                                (int)$shape['shape_id'],
                                $i
                            ); ?>
                            <label class="option-thumb <?= $chosen ? 'selected' : '' ?>">
                                <input type="radio" name="shape_id" value="<?= (int)$shape['shape_id'] ?>"
                                       data-shape="<?= e(shape_key($shape['name'])) ?>"
                                       data-name="<?= e($shape['name']) ?>" <?= $chosen ? 'checked' : '' ?>>
                                <img src="<?= upload_url('shapes', $shape['image_path']) ?>"
                                     alt="" loading="lazy">
                                <span class="option-name"><?= e($shape['name']) ?></span>
                                <span class="check" aria-hidden="true"><?= icon('check', 13) ?></span>
                            </label>
                        <?php endforeach; ?>
                            </div>
                        </div>
                        <button type="button" class="carousel-nav next" data-carousel-next
                                aria-label="More shapes"><?= icon('chevron-right', 17) ?></button>
                    </div>
</fieldset>

                <fieldset class="studio-card">
                    <legend class="visually-hidden">Choose your design</legend>
                    <div class="studio-card-head">
                        <span class="studio-step-num" aria-hidden="true">2</span>
                        <h3>Choose your design</h3>
                        <span class="hint"><?= count($designs) ?> options</span>
                    </div>
                                        <?php /* Four at a time rather than a wall of options. Every option is
                             still rendered and still a radio in this form, so the preview
                             and the submitted value are unchanged - the carousel only
                             controls how many are on screen. */ ?>
                    <div class="carousel option-carousel" data-carousel
                         data-per-view="4 3 2" data-min-card="104"
                         data-carousel-item=".option-thumb">
                        <button type="button" class="carousel-nav prev" data-carousel-prev
                                aria-label="Previous designs"><?= icon('chevron-left', 17) ?></button>
                        <div class="carousel-viewport">
                            <div class="carousel-track" data-carousel-track>

                        <?php foreach ($designs as $i => $design): ?>
                            <?php $chosen = $isChosen(
                                isset($savedDesign['design_id']) ? (int)$savedDesign['design_id'] : null,
                                (int)$design['design_id'],
                                $i
                            ); ?>
                            <label class="option-thumb <?= $chosen ? 'selected' : '' ?>">
                                <input type="radio" name="design_id" value="<?= (int)$design['design_id'] ?>"
                                       data-img="<?= upload_url('designs', $design['image_path']) ?>"
                                       data-name="<?= e($design['name']) ?>" <?= $chosen ? 'checked' : '' ?>>
                                <img src="<?= upload_url('designs', $design['image_path']) ?>"
                                     alt="" loading="lazy">
                                <span class="option-name"><?= e($design['name']) ?></span>
                                <span class="check" aria-hidden="true"><?= icon('check', 13) ?></span>
                            </label>
                        <?php endforeach; ?>
                            </div>
                        </div>
                        <button type="button" class="carousel-nav next" data-carousel-next
                                aria-label="More designs"><?= icon('chevron-right', 17) ?></button>
                    </div>
</fieldset>

                <div class="studio-card">
                    <div class="studio-card-head">
                        <span class="studio-step-num" aria-hidden="true">3</span>
                        <h3>Upload your image</h3>
                        <span class="hint">Optional</span>
                    </div>
                    <div data-dropzone data-max-bytes="<?= MAX_UPLOAD_BYTES ?>"
                         data-accept="jpg,jpeg,png,webp">
                        <label class="dropzone" for="custom_image">
                            <span class="dz-icon" aria-hidden="true"><?= icon('upload', 20) ?></span>
                            <span class="dz-title">Drop a photo here, or click to choose one</span>
                            <span class="dz-hint">JPG, PNG or WEBP &middot; up to
                                <?= (int)(MAX_UPLOAD_BYTES / 1048576) ?>MB</span>
                            <input type="file" id="custom_image" name="custom_image"
                                   accept=".jpg,.jpeg,.png,.webp" data-dz-input>
                        </label>
                        <p class="dz-error" data-dz-error role="alert" hidden></p>
                        <div class="dz-file" data-dz-preview>
                            <img src="" alt="" data-dz-thumb>
                            <span class="dz-name" data-dz-name></span>
                            <button type="button" class="link-remove" data-dz-clear>
                                <?= icon('close', 14) ?> Remove
                            </button>
                        </div>
                    </div>

                    <?php if (!empty($savedDesign['custom_image_path'])): ?>
                        <!-- The photo already stored on this saved design. It is kept
                             unless a new file is chosen or this box is ticked. -->
                        <div class="saved-photo">
                            <img src="<?= e(upload_url('customizations', $savedDesign['custom_image_path'])) ?>"
                                 alt="The photo saved with this design" width="48" height="48">
                            <span>Saved photo in use</span>
                            <label class="form-check-label d-flex align-items-center gap-2">
                                <input class="form-check-input" type="checkbox" name="remove_image" value="1">
                                Remove it
                            </label>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="studio-card">
                    <div class="studio-card-head">
                        <span class="studio-step-num" aria-hidden="true">4</span>
                        <h3>Add your text</h3>
                        <span class="hint">Optional</span>
                    </div>
                    <label class="form-label" for="custom_text">
                        Personalized text <span class="optional">&mdash; a name, quote or short dedication</span>
                    </label>
                    <input type="text" id="custom_text" name="custom_text" class="form-control"
                           maxlength="<?= CUSTOM_TEXT_MAX ?>" placeholder="e.g. Just One More..."
                           value="<?= e($savedDesign['custom_text'] ?? '') ?>">
                    <p class="char-count" id="charCount" aria-live="polite">
                        0 / <?= CUSTOM_TEXT_MAX ?> characters
                    </p>
                    <p class="form-text">A bookmark has room for a short line. Anything longer is trimmed to
                        <?= CUSTOM_TEXT_MAX ?> characters when it is saved.</p>
                </div>

                <fieldset class="studio-card">
                    <legend class="visually-hidden">Preview colour</legend>
                    <div class="studio-card-head">
                        <span class="studio-step-num" aria-hidden="true">5</span>
                        <h3>Choose your colour</h3>
                        <span class="hint">Preview only</span>
                    </div>

                    <div class="color-controls" data-color-picker>
                        <div class="color-pick-row">
                            <label class="visually-hidden" for="colorWell">Pick a colour</label>
                            <input type="color" id="colorWell" class="color-well" value="#2f6f5e" data-color-well>

                            <div>
                                <label class="form-label" for="colorHex" style="margin-bottom:.25rem">Hex</label>
                                <input type="text" id="colorHex" class="form-control color-hex"
                                       value="#2F6F5E" maxlength="7" spellcheck="false"
                                       data-color-hex>
                            </div>
                        </div>

                        <div class="rgb-grid">
                            <?php foreach ([['r', 'Red', 47], ['g', 'Green', 111], ['b', 'Blue', 94]] as [$ch, $chLabel, $chVal]): ?>
                                <div class="rgb-field">
                                    <label for="rgb<?= $ch ?>"><?= e($chLabel) ?></label>
                                    <input type="number" id="rgb<?= $ch ?>" min="0" max="255" step="1"
                                           value="<?= $chVal ?>" class="form-control" data-rgb="<?= $ch ?>">
                                    <input type="range" min="0" max="255" step="1" value="<?= $chVal ?>"
                                           aria-label="<?= e($chLabel) ?> amount" tabindex="-1"
                                           data-rgb-range="<?= $ch ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Quick starting points; the controls above are the real picker. -->
                        <div class="swatch-row swatch-row-compact" role="group" aria-label="Preset colours">
                            <?php foreach ($tints as [$hex, $name]): ?>
                                <button type="button" class="swatch" data-color-preset="<?= e($hex) ?>"
                                        title="<?= e($name) ?>">
                                    <span class="dot" style="background:<?= e($hex) ?>" aria-hidden="true"></span>
                                    <span class="visually-hidden"><?= e($name) ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <p class="form-text" style="margin-top:var(--s-3)">This colours the preview so you can
                        picture the finished bookmark. The stock we print on follows the design chosen in
                        step 2, so the colour is not part of the order.</p>
                </fieldset>
            </div>

            <!-- ---------------------- live preview ---------------------- -->
            <aside class="preview-panel" aria-label="Live preview">
                <div class="preview-head">
                    <h2>Live preview</h2>
                    <span class="live-dot">Updating</span>
                </div>

                <div class="preview-stage">
                    <div class="bm-preview" id="bmPreview" data-shape="classic" role="img"
                         aria-label="Preview of your customized bookmark">
                        <span class="bm-tassel" aria-hidden="true"></span>
                        <span class="bm-hole" aria-hidden="true"></span>
                        <img class="bm-photo" id="bmPhoto" src="" alt="">
                        <span class="bm-pattern" id="bmPattern" aria-hidden="true"></span>
                        <span class="bm-text is-placeholder" id="bmText"
                              data-placeholder="Your text appears here">Your text appears here</span>
                    </div>
                </div>

                <div class="preview-summary">
                    <div class="row-line"><span>Shape</span><strong data-summary="shape">&mdash;</strong></div>
                    <div class="row-line"><span>Design</span><strong data-summary="design">&mdash;</strong></div>
                    <div class="row-line"><span>Photo</span><strong data-summary="photo">None</strong></div>
                    <div class="row-line"><span>Text</span><strong data-summary="text">None</strong></div>
                </div>

                <div class="preview-foot">
                    <div class="preview-price">
                        <span class="label">Price each</span>
                        <span class="amount mono-num"><?= format_price($product['price']) ?></span>
                    </div>

                    <div class="qty-row">
                        <label class="form-label" for="qtyStudio" style="margin:0">Quantity</label>
                        <div class="qty-stepper">
                            <button type="button" data-step="-1" aria-label="Decrease quantity"><?= icon('minus', 16) ?></button>
                            <input type="number" id="qtyStudio" name="quantity" value="1" min="1"
                                   max="<?= $stock ?>" data-max-stock="<?= $stock ?>">
                            <button type="button" data-step="1" aria-label="Increase quantity"><?= icon('plus', 16) ?></button>
                        </div>
                    </div>

                    <!-- Updated live as the quantity changes. place_order.php still
                         recomputes every line from the products table, so this is a
                         display of the maths, never its source. -->
                    <div class="line-total-row">
                        <span class="lbl">Item total</span>
                        <span class="amt mono-num" data-line-total
                              data-unit-price="<?= (float)$product['price'] ?>">
                            <?= format_price($product['price']) ?>
                        </span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block" data-busy="Adding&hellip;">
                        <?= icon('cart', 18) ?> Add to cart
                    </button>

                    <!-- Saving posts the same fields to design_save.php instead of
                         add_to_cart.php, so one set of controls serves both actions. -->
                    <div class="save-design">
                        <label class="form-label" for="design_name">
                            Design name <span class="optional">&mdash; so you can find it again</span>
                        </label>
                        <input type="text" id="design_name" name="design_name" class="form-control"
                               maxlength="100" placeholder="e.g. Mama&rsquo;s bookmark"
                               value="<?= e($savedDesign['design_name'] ?? '') ?>">
                        <button type="submit" class="btn btn-secondary btn-block"
                                formaction="<?= BASE_URL ?>/customer/design_save.php" formnovalidate>
                            <?= icon('save', 17) ?>
                            <?= $savedDesign ? 'Update saved design' : 'Save design' ?>
                        </button>
                        <?php if ($savedDesign): ?>
                            <a class="btn btn-quiet btn-sm btn-block"
                               href="<?= BASE_URL ?>/customer/my_designs.php">Back to saved designs</a>
                        <?php endif; ?>
                    </div>

                    <p class="summary-note">
                        <?= icon('info', 15) ?>
                        <span>Made to order. We&rsquo;ll craft your bookmark exactly as previewed.</span>
                    </p>
                </div>
            </aside>
        </form>
    </div>
</section>
<?php endif; ?>

<div class="container page-shell">

<?php if ($related): ?>
    <section style="margin-top:var(--s-12)">
        <div class="section-head section-head-row" style="margin-bottom:var(--s-6)">
            <div class="d-flex flex-column gap-2">
                <p class="eyebrow">You might also like</p>
                <h2>More in <?= e($product['category_name']) ?></h2>
            </div>
            <a href="<?= BASE_URL ?>/customer/shop.php?category=<?= (int)$product['category_id'] ?>"
               class="btn btn-secondary btn-sm">View category <?= icon('arrow-right', 15) ?></a>
        </div>
        <div class="product-grid product-grid-4">
            <?php foreach ($related as $product): require __DIR__ . '/_product_card.php'; endforeach; ?>
        </div>
    </section>
<?php endif; ?>

</div><!-- /container -->

<?php require __DIR__ . '/../includes/customer/footer.php'; ?>
