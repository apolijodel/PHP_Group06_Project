<?php
/**
 * Renders a list of order/cart lines: image, name, customization, quantity,
 * unit price and line total.
 *
 * Shared by the checkout review, the order confirmation and the order detail
 * page, so a customer sees the same summary at every step of the flow.
 *
 * Expects $lines — each entry:
 *   image  string|null  absolute image URL (null renders a neutral placeholder)
 *   name   string
 *   custom string       one-line customization summary, '' when ready-made
 *   qty    int
 *   unit   float        unit price
 *   total  float        line total
 */
$lines = $lines ?? [];
?>
<ul class="order-lines">
    <?php foreach ($lines as $line): ?>
        <li class="order-line">
            <span class="ol-media">
                <?php if (!empty($line['image'])): ?>
                    <img src="<?= e($line['image']) ?>" alt="" width="56" height="56" loading="lazy">
                <?php else: ?>
                    <span class="ol-media-fallback" aria-hidden="true"><?= icon('package', 20) ?></span>
                <?php endif; ?>
            </span>

            <span class="ol-main">
                <span class="ol-name"><?= e($line['name']) ?></span>
                <?php if (!empty($line['custom'])): ?>
                    <span class="ol-custom"><?= e($line['custom']) ?></span>
                <?php endif; ?>
                <span class="ol-qty">
                    Qty: <?= (int)$line['qty'] ?>
                    <span class="ol-unit mono-num">&times; <?= format_price((float)$line['unit']) ?></span>
                </span>
            </span>

            <span class="ol-total mono-num"><?= format_price((float)$line['total']) ?></span>
        </li>
    <?php endforeach; ?>
</ul>
