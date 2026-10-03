<?php
/**
 * Saved Designs — the customer's own library of reusable bookmark designs.
 *
 * These are real rows in `saved_designs`, created from the customization
 * studio, not a read of order history. Each one reopens the studio with its
 * configuration loaded — to order it, or to change it and save again — or can
 * be deleted.
 */
require_once __DIR__ . '/../includes/auth.php';
require_login();

$stmt = db()->prepare(
    'SELECT sd.*,
            s.name  AS shape_name,
            d.name  AS design_name_label,
            d.image_path AS design_image,
            p.name  AS product_name,
            p.price AS product_price,
            p.stock_quantity,
            p.is_customizable
       FROM saved_designs sd
       LEFT JOIN shapes   s ON s.shape_id   = sd.shape_id
       LEFT JOIN designs  d ON d.design_id  = sd.design_id
       LEFT JOIN products p ON p.product_id = sd.product_id
      WHERE sd.user_id = :uid
      ORDER BY sd.updated_at DESC'
);
$stmt->execute(['uid' => current_user()['user_id']]);
$designs = $stmt->fetchAll();

$pageTitle = 'Saved Designs';
require __DIR__ . '/../includes/customer/header.php';
?>

<div class="account-page">
    <div>
        <div class="page-head">
            <div>
                <p class="eyebrow">Account</p>
                <h1 style="margin-top:var(--s-2)">Saved designs</h1>
                <p>Bookmarks you&rsquo;ve designed and kept. Reuse one any time, or edit it and save again.</p>
            </div>
            <a href="<?= BASE_URL ?>/customer/shop.php?customizable=1" class="btn btn-primary btn-sm">
                <?= icon('wand', 15) ?> New design
            </a>
        </div>

        <?php if (!$designs): ?>
            <div class="card empty-state">
                <span class="empty-icon"><?= icon('palette', 26) ?></span>
                <h2>No saved designs yet</h2>
                <p>Personalize a bookmark &mdash; shape, design, photo and text &mdash; then press
                    <strong>Save design</strong> in the studio. It will be kept here so you can order it again
                    without starting over.</p>
                <a href="<?= BASE_URL ?>/customer/shop.php?customizable=1" class="btn btn-primary"
                   style="margin-top:var(--s-4)">
                    <?= icon('wand', 16) ?> Start customizing
                </a>
            </div>
        <?php else: ?>
            <div class="product-grid product-grid-4">
                <?php foreach ($designs as $d): ?>
                    <?php
                    $pattern = $d['design_image']
                        ? "url('" . upload_url('designs', $d['design_image']) . "')"
                        : 'none';
                    $stock = (int)($d['stock_quantity'] ?? 0);
                    // A design can outlive its product: the product may have been
                    // deleted, made non-customizable, or sold out.
                    $canReuse = $d['product_id'] !== null && $d['is_customizable'] && $stock > 0;
                    ?>
                    <article class="design-card">
                        <div class="design-stage">
                            <div class="bm-preview" data-shape="<?= e(shape_key($d['shape_name'])) ?>"
                                 role="img"
                                 aria-label="<?= e($d['design_name']) ?> preview">
                                <span class="bm-tassel" aria-hidden="true"></span>
                                <span class="bm-hole" aria-hidden="true"></span>
                                <?php if ($d['custom_image_path']): ?>
                                    <img class="bm-photo show"
                                         src="<?= e(upload_url('customizations', $d['custom_image_path'])) ?>"
                                         alt="" loading="lazy">
                                <?php endif; ?>
                                <span class="bm-pattern" style="--bm-pattern:<?= $pattern ?>" aria-hidden="true"></span>
                                <span class="bm-text"><?= e($d['custom_text'] ?: '') ?></span>
                            </div>
                        </div>

                        <div class="design-body">
                            <h2 class="design-name"><?= e($d['design_name']) ?></h2>

                            <div class="custom-summary">
                                <?php if ($d['shape_name']): ?>
                                    <span class="custom-tag"><span class="k">Shape</span> <?= e($d['shape_name']) ?></span>
                                <?php endif; ?>
                                <?php if ($d['design_name_label']): ?>
                                    <span class="custom-tag"><span class="k">Design</span> <?= e($d['design_name_label']) ?></span>
                                <?php endif; ?>
                                <?php if ($d['custom_image_path']): ?>
                                    <span class="custom-tag"><span class="k">Photo</span> Added</span>
                                <?php endif; ?>
                            </div>

                            <?php if ($d['custom_text']): ?>
                                <p class="design-quote">&ldquo;<?= e($d['custom_text']) ?>&rdquo;</p>
                            <?php endif; ?>

                            <div class="design-meta">
                                <span>
                                    <?php if ($d['product_name']): ?>
                                        <?= e($d['product_name']) ?>
                                    <?php else: ?>
                                        Product no longer available
                                    <?php endif; ?>
                                </span>
                                <span>&middot;</span>
                                <span>Saved <?= e(date('M j, Y', strtotime($d['created_at']))) ?></span>
                            </div>

                            <?php if (!$canReuse): ?>
                                <p class="form-text" style="margin:0">
                                    <?php if ($d['product_id'] === null): ?>
                                        The bookmark this design was made for is no longer sold.
                                    <?php elseif (!$d['is_customizable']): ?>
                                        This bookmark is no longer customizable.
                                    <?php else: ?>
                                        <?= e($d['product_name']) ?> is out of stock right now.
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="design-actions">
                            <?php if ($canReuse): ?>
                                <?php
                                // Both actions open the studio with this design
                                // loaded: "Use" to order it, "Edit" to change and
                                // save it again. One screen, one prefill path.
                                $studioUrl = BASE_URL . '/customer/product.php?id=' . (int)$d['product_id']
                                    . '&saved=' . (int)$d['saved_design_id'] . '#studio';
                                ?>
                                <a class="btn btn-primary btn-sm" href="<?= $studioUrl ?>">
                                    <?= icon('wand', 15) ?> Use design
                                </a>
                                <a class="btn btn-secondary btn-sm" href="<?= $studioUrl ?>">
                                    <?= icon('edit', 15) ?> Edit
                                </a>
                            <?php else: ?>
                                <a class="btn btn-secondary btn-sm"
                                   href="<?= BASE_URL ?>/customer/shop.php?customizable=1">
                                    <?= icon('search', 15) ?> Find a bookmark
                                </a>
                            <?php endif; ?>

                            <form method="post" action="<?= BASE_URL ?>/customer/design_delete.php"
                                  data-confirm
                                  data-confirm-title="Delete saved design?"
                                  data-confirm-body="Delete &quot;<?= e($d['design_name']) ?>&quot;?"
                                  data-confirm-action="Delete design">
                                <?= csrf_field() ?>
                                <input type="hidden" name="saved_design_id" value="<?= (int)$d['saved_design_id'] ?>">
                                <button type="submit" class="btn btn-danger-quiet btn-sm"
                                        aria-label="Delete <?= e($d['design_name']) ?>">
                                    <?= icon('trash', 15) ?> Delete
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../includes/customer/footer.php'; ?>
