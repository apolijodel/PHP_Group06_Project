<?php
$requireCapability = 'categories.manage';
$pageTitle = 'Categories';
require __DIR__ . '/includes/admin_header.php';

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$editing = null;
if ($editId) {
    $stmt = db()->prepare('SELECT * FROM categories WHERE category_id = :id');
    $stmt->execute(['id' => $editId]);
    $editing = $stmt->fetch() ?: null;
}

$categories = db()->query(
    'SELECT c.*, COUNT(p.product_id) AS product_count
     FROM categories c LEFT JOIN products p ON p.category_id = c.category_id
     GROUP BY c.category_id ORDER BY c.name'
)->fetchAll();
?>

<div class="page-head">
    <div>
        <p class="eyebrow eyebrow-muted">Catalog</p>
        <h2 style="margin-top:var(--s-2)">Categories</h2>
        <p>How products are grouped in the shop filters.</p>
    </div>
    <?php /* No "Add New Category" button here by request. The form beside the
             table is the creation flow and is always visible, so the button
             was a second door into the same room. Category creation itself is
             untouched: category_save.php still handles both insert and
             update. */ ?>
</div>

<div class="cart-grid">
    <div class="data-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <caption class="visually-hidden">Product categories</caption>
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Description</th>
                        <th scope="col">Products</th>
                        <th scope="col">Status</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <th scope="row" style="font-weight:600"><?= e($cat['name']) ?></th>
                            <td class="text-muted" style="font-size:.88rem"><?= e($cat['description'] ?: '—') ?></td>
                            <td class="mono-num"><?= (int)$cat['product_count'] ?></td>
                            <td>
                                <?php /* Derived from the data rather than a stored flag: nothing in the
                                         storefront would honour an "inactive" category, so a column for
                                         it would be a switch that does nothing. */ ?>
                                <?php if ((int)$cat['product_count'] > 0): ?>
                                    <span class="badge bg-success">In use</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Empty</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= BASE_URL ?>/admin/categories.php?edit=<?= (int)$cat['category_id'] ?>"
                                       class="btn btn-secondary btn-sm"><?= icon('edit', 14) ?> Edit</a>
                                    <?php $inUse = (int)$cat['product_count']; ?>
                                    <form method="post" action="<?= BASE_URL ?>/admin/category_delete.php"
                                          data-confirm
                                          data-confirm-title="Delete category?"
                                          data-confirm-body="<?= $inUse
                                              ? e($cat['name']) . ' still has ' . $inUse . ' product'
                                                . ($inUse === 1 ? '' : 's') . ' in it.'
                                              : 'Delete &quot;' . e($cat['name']) . '&quot;?' ?>"
                                          data-confirm-note="<?= $inUse
                                              ? 'Move them to another category first — deleting would leave them without one.'
                                              : 'This action cannot be undone.' ?>"
                                          data-confirm-action="<?= $inUse ? 'Try anyway' : 'Delete category' ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="category_id" value="<?= (int)$cat['category_id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                                aria-label="Delete <?= e($cat['name']) ?>"
                                                title="Delete <?= e($cat['name']) ?>">
                                            <?= icon('trash', 14) ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$categories): ?>
                        <tr><td colspan="5" class="text-muted">No categories yet. Add one to get started.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <aside class="summary-card">
        <h2 id="categoryForm"><?= $editing ? 'Edit category' : 'Add category' ?></h2>
        <div class="summary-body">
            <form method="post" action="<?= BASE_URL ?>/admin/category_save.php">
                <?= csrf_field() ?>
                <?php if ($editing): ?>
                    <input type="hidden" name="category_id" value="<?= (int)$editing['category_id'] ?>">
                <?php endif; ?>

                <div class="field-group">
                    <label class="form-label" for="catName">Name</label>
                    <input type="text" id="catName" name="name" class="form-control"
                           value="<?= e($editing['name'] ?? '') ?>" required>
                </div>
                <div class="field-group">
                    <label class="form-label" for="catDesc">
                        Description <span class="optional">&mdash; optional</span>
                    </label>
                    <textarea id="catDesc" name="description" class="form-control" rows="3"><?= e($editing['description'] ?? '') ?></textarea>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?= $editing ? 'Save changes' : 'Add category' ?>
                    </button>
                    <?php if ($editing): ?>
                        <a href="<?= BASE_URL ?>/admin/categories.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </aside>
</div>

<?php require __DIR__ . '/../includes/backoffice/shell_footer.php'; ?>
