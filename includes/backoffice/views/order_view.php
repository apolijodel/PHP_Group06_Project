<?php
/**
 * Screen: order_view - markup.
 *
 * Rendered inside whichever panel shell required it - the administrator
 * panel and the staff panel share this one copy.
 *
 * Auth and form handling happen in includes/backoffice/screens/order_view.php, before any
 * output. Read-only queries that were already here stayed here rather
 * than being moved for the sake of it.
 */
?>
<?php 
$orderId = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare(
    'SELECT o.*, u.username, u.email FROM orders o
     JOIN users u ON u.user_id = o.user_id WHERE o.order_id = :id'
);
$stmt->execute(['id' => $orderId]);
$order = $stmt->fetch();

if (!$order) {
    ?>
    <div class="card empty-state">
        <span class="empty-icon"><?= icon('receipt', 24) ?></span>
        <h2>Order not found</h2>
        <p>It may have been deleted, or the link might be out of date.</p>
        <a href="<?= panel_url('orders.php') ?>" class="btn btn-primary" style="margin-top:var(--s-4)">
            Back to orders
        </a>
    </div>
    <?php
    return;   // the panel page closes the shell
}

$itemStmt = db()->prepare(
    'SELECT oi.*, p.image_path
       FROM order_items oi
       LEFT JOIN products p ON p.product_id = oi.product_id
      WHERE oi.order_id = :id
      ORDER BY oi.order_item_id'
);
$itemStmt->execute(['id' => $orderId]);
$items = $itemStmt->fetchAll();

/**
 * Lines that someone actually has to make something for. Ready-made lines are
 * left out: a carousel of stock photos would be decoration, not information.
 */
$customLines = array_values(array_filter($items, static fn($item) => (bool)(
    $item['shape_name_snapshot'] || $item['design_name_snapshot']
    || $item['custom_text'] || $item['custom_image_path']
)));

// Logical status workflow: only forward transitions (or cancellation) are allowed.
$transitions = [
    'Pending' => ['Processing', 'Cancelled'],
    'Processing' => ['On Shipping', 'Cancelled'],
    'On Shipping' => ['Completed'],
    'Completed' => [],
    'Cancelled' => [],
];
$nextOptions = $transitions[$order['status']] ?? [];

$cancelled = $order['status'] === 'Cancelled';
$flow = order_statuses();
$currentIndex = array_search($order['status'], $flow, true);
?>

<nav class="breadcrumbs" aria-label="Breadcrumb">
    <a href="<?= panel_url('orders.php') ?>">Orders</a>
    <span class="sep" aria-hidden="true"><?= icon('chevron-right', 13) ?></span>
    <span aria-current="page">#<?= (int)$order['order_id'] ?></span>
</nav>

<div class="page-head">
    <div>
        <p class="eyebrow eyebrow-muted">Sales</p>
        <h2 class="mono-num" style="margin-top:var(--s-2)">Order #<?= (int)$order['order_id'] ?></h2>
        <p>Placed <?= e(date('M j, Y \a\t g:i A', strtotime($order['created_at']))) ?>
            &middot; last updated <?= e(date('M j, Y \a\t g:i A', strtotime($order['updated_at']))) ?></p>
    </div>
    <span class="badge bg-<?= status_variant($order['status']) ?>" style="font-size:.85rem;padding:.5em 1em">
        <?= e($order['status']) ?>
    </span>
</div>

<div class="cart-grid">
    <div class="d-flex flex-column" style="gap:var(--s-5)">
        <?php if ($customLines): ?>
            <!-- ------------------------ what to make ------------------------
                 The Items table below is the money record. This is the bench
                 copy: the customer's own photo at a size you can actually work
                 from, with the shape, design and text that go with it. Only
                 customized lines appear here. -->
            <section class="card card-flush">
                <div class="panel-head">
                    <h2>What to make</h2>
                    <span class="text-muted" style="font-size:.85rem">
                        <?= count($customLines) ?> customized
                        <?= count($customLines) === 1 ? 'line' : 'lines' ?>
                    </span>
                </div>
                <div class="panel-body">
                    <div class="carousel admin-carousel" data-carousel data-per-view="2 2 1"
                         data-carousel-item=".ad-card">
                        <button type="button" class="carousel-nav prev" data-carousel-prev
                                aria-label="Previous item"><?= icon('chevron-left', 18) ?></button>
                        <div class="carousel-viewport">
                            <div class="carousel-track" data-carousel-track>
                                <?php foreach ($customLines as $item): ?>
                                    <?php
                                    $photo = $item['custom_image_path']
                                        ? upload_url('customizations', $item['custom_image_path'])
                                        : ($item['image_path'] ? upload_url('products', $item['image_path']) : '');
                                    ?>
                                    <article class="ad-card">
                                        <?php if ($photo && $item['custom_image_path']): ?>
                                            <a class="ad-card-media" href="<?= e($photo) ?>"
                                               aria-label="Open the customer's photo full size">
                                                <img src="<?= e($photo) ?>" alt="" loading="lazy">
                                                <span class="badge badge-cat">Customer photo</span>
                                            </a>
                                        <?php else: ?>
                                            <span class="ad-card-media">
                                                <?php if ($photo): ?>
                                                    <img src="<?= e($photo) ?>" alt="" loading="lazy">
                                                <?php else: ?>
                                                    <span class="ad-card-fallback" aria-hidden="true">
                                                        <?= icon('image', 26) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>

                                        <div class="ad-card-body">
                                            <p class="ad-card-name"><?= e($item['product_name_snapshot']) ?></p>
                                            <dl class="ad-card-specs">
                                                <?php if ($item['shape_name_snapshot']): ?>
                                                    <div>
                                                        <dt>Shape</dt>
                                                        <dd><?= e($item['shape_name_snapshot']) ?></dd>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($item['design_name_snapshot']): ?>
                                                    <div>
                                                        <dt>Design</dt>
                                                        <dd><?= e($item['design_name_snapshot']) ?></dd>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($item['custom_text']): ?>
                                                    <div>
                                                        <dt>Text</dt>
                                                        <dd>&ldquo;<?= e($item['custom_text']) ?>&rdquo;</dd>
                                                    </div>
                                                <?php endif; ?>
                                            </dl>
                                        </div>

                                        <div class="ad-card-foot">
                                            <span class="ad-card-meta"><span class="k">Make</span><span
                                                class="mono-num"><?= (int)$item['quantity'] ?></span></span>
                                            <span class="mono-num" style="font-weight:600"><?=
                                                format_price($item['line_total']) ?></span>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button type="button" class="carousel-nav next" data-carousel-next
                                aria-label="Next item"><?= icon('chevron-right', 18) ?></button>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- ---------------------------- items ---------------------------- -->
        <section class="card card-flush">
            <div class="panel-head">
                <h2>Items</h2>
                <span class="text-muted" style="font-size:.85rem"><?= count($items) ?> <?= count($items) === 1 ? 'line' : 'lines' ?></span>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <caption class="visually-hidden">Items in this order</caption>
                    <thead>
                        <tr>
                            <th scope="col">Product</th>
                            <th scope="col">Customization</th>
                            <th scope="col">Unit price</th>
                            <th scope="col">Qty</th>
                            <th scope="col">Line total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <th scope="row" style="font-weight:600"><?= e($item['product_name_snapshot']) ?></th>
                                <td>
                                    <?php if ($item['shape_name_snapshot'] || $item['design_name_snapshot'] || $item['custom_text'] || $item['custom_image_path']): ?>
                                        <div class="custom-summary">
                                            <?php if ($item['shape_name_snapshot']): ?>
                                                <span class="custom-tag"><span class="k">Shape</span> <?= e($item['shape_name_snapshot']) ?></span>
                                            <?php endif; ?>
                                            <?php if ($item['design_name_snapshot']): ?>
                                                <span class="custom-tag"><span class="k">Design</span> <?= e($item['design_name_snapshot']) ?></span>
                                            <?php endif; ?>
                                            <?php if ($item['custom_text']): ?>
                                                <span class="custom-tag"><span class="k">Text</span> &ldquo;<?= e($item['custom_text']) ?>&rdquo;</span>
                                            <?php endif; ?>
                                            <?php if ($item['custom_image_path']): ?>
                                                <a class="custom-tag"
                                                   href="<?= upload_url('customizations', $item['custom_image_path']) ?>">
                                                    <img src="<?= upload_url('customizations', $item['custom_image_path']) ?>"
                                                         alt="">
                                                    Photo <?= icon('external', 12) ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size:.85rem">Ready-made</span>
                                    <?php endif; ?>
                                </td>
                                <td class="mono-num"><?= format_price($item['unit_price']) ?></td>
                                <td class="mono-num"><?= (int)$item['quantity'] ?></td>
                                <td class="mono-num" style="font-weight:600"><?= format_price($item['line_total']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Order total</th>
                            <th class="mono-num"><?= format_price($order['total_amount']) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        <!-- --------------------- customer & delivery --------------------- -->
        <section class="card card-flush">
            <div class="panel-head"><h2>Customer &amp; delivery</h2></div>
            <div class="panel-body">
                <div class="form-grid">
                    <div>
                        <p class="eyebrow eyebrow-muted">Account</p>
                        <p style="margin-top:var(--s-2)">
                            <strong>@<?= e($order['username']) ?></strong><br>
                            <a href="mailto:<?= e($order['email']) ?>"><?= e($order['email']) ?></a>
                        </p>
                    </div>
                    <div>
                        <p class="eyebrow eyebrow-muted">Payment</p>
                        <p style="margin-top:var(--s-2)" class="d-flex align-items-center gap-2">
                            <?= icon($order['payment_method'] === 'Bank Transfer' ? 'bank' : 'wallet', 18) ?>
                            <strong><?= e($order['payment_method']) ?></strong>
                        </p>
                    </div>
                    <div class="span-2">
                        <p class="eyebrow eyebrow-muted">Ship to</p>
                        <p style="margin-top:var(--s-2)">
                            <strong><?= e($order['full_name']) ?></strong>
                            &middot; <span class="text-muted"><?= e($order['contact_number']) ?></span><br>
                            <span class="text-muted"><?= e($order['delivery_address']) ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- --------------------------- status panel --------------------------- -->
    <aside class="summary-card">
        <h2>Status</h2>
        <div class="summary-body">
            <?php if ($cancelled): ?>
                <ol class="timeline is-cancelled">
                    <li class="is-done"><span class="t-label">Order placed</span></li>
                    <li class="is-current"><span class="t-label">Cancelled</span>
                        <span class="t-note">No further transitions are allowed.</span></li>
                </ol>
            <?php else: ?>
                <ol class="timeline">
                    <?php foreach ($flow as $i => $stage): ?>
                        <li class="<?= $i < $currentIndex ? 'is-done' : ($i === $currentIndex ? 'is-current' : '') ?>">
                            <span class="t-label"><?= e($stage) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>

        <div class="summary-foot">
            <?php if ($nextOptions): ?>
                <form method="post" action="<?= BASE_URL ?>/admin/order_status_update.php"
                      class="d-flex flex-column gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="order_id" value="<?= (int)$order['order_id'] ?>">
                    <label class="form-label" for="statusSelect">Move this order to</label>
                    <select id="statusSelect" name="status" class="form-select">
                        <?php foreach ($nextOptions as $optStatus): ?>
                            <option value="<?= e($optStatus) ?>"><?= e($optStatus) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-block">
                        <?= icon('check', 16) ?> Update status
                    </button>
                </form>
            <?php else: ?>
                <p class="summary-note">
                    <?= icon('info', 15) ?>
                    <span>This order is in a final state and can no longer be changed.</span>
                </p>
            <?php endif; ?>

            <a href="<?= panel_url('orders.php') ?>" class="btn btn-secondary btn-block">
                <?= icon('arrow-left', 16) ?> Back to orders
            </a>
        </div>
    </aside>
</div>
