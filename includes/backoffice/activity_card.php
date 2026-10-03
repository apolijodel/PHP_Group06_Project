<?php
/**
 * One audit-trail event, as a carousel card.
 *
 * Included both by the dashboard (first batch) and by activity_feed.php (every
 * batch after it), so the two can never drift apart. Expects $event.
 */
$ev = $event;
[$evIcon, $evTone] = activity_style($ev['action']);

/**
 * Where this event happened, when the signed-in role is allowed to go there.
 * An event about a deleted row keeps its text and simply loses its link.
 */
$evLink = null;
switch ($ev['entity_type']) {
    case 'order':
        if (can('orders.view')) {
            $evLink = panel_url('order_view.php?id=' . (int)$ev['entity_id']);
        }
        break;
    case 'product':
        if (can('products.manage')) {
            $evLink = BASE_URL . '/admin/product_form.php?id=' . (int)$ev['entity_id'];
        } elseif (can('products.view')) {
            $evLink = panel_url('products.php');
        }
        break;
    case 'user':
        if ($ev['actor_role'] === 'customer' && can('customers.view')) {
            $evLink = panel_url('customer_view.php?id=' . (int)$ev['entity_id']);
        }
        break;
    case 'category':
        if (can('categories.manage')) {
            $evLink = BASE_URL . '/admin/categories.php';
        }
        break;
}

$evWhen = (string)$ev['created_at'];
?>
<article class="ad-card is-event">
    <div class="ad-card-body">
        <p class="event-top">
            <span class="event-icon tone-<?= e($evTone) ?>" aria-hidden="true"><?= icon($evIcon, 15) ?></span>
            <span class="badge badge-role r-<?= e($ev['actor_role']) ?>">
                <?= e(role_label($ev['actor_role'])) ?>
            </span>
        </p>
        <p class="ad-card-name"><?= e($ev['actor_name']) ?></p>
        <p class="event-summary"><?= e($ev['summary']) ?></p>
    </div>
    <div class="ad-card-foot">
        <span class="ad-card-meta" title="<?= e(date('M j, Y \a\t g:i A', strtotime($evWhen))) ?>">
            <?= e(time_ago($evWhen)) ?>
        </span>
        <?php if ($evLink): ?>
            <a href="<?= e($evLink) ?>" class="btn btn-secondary btn-sm">
                View <?= icon('chevron-right', 13) ?>
            </a>
        <?php endif; ?>
    </div>
</article>
