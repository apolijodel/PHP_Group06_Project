<?php
/**
 * Save a design — create or edit.
 *
 * A new design is stored as 'pending' and is invisible to customers until an
 * administrator approves it on /admin/option_review.php. Editing an approved design's image
 * sends it back for review, because the picture is the thing that was approved;
 * renaming one does not.
 *
 * The file is validated and malware-scanned by handle_image_upload() before any
 * row is written, and the write happens in a transaction, so a failure cannot
 * leave a record pointing at a file that was never stored.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_can('options.manage');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/designs.php');
}
csrf_check();

$id = !empty($_POST['design_id']) ? (int)$_POST['design_id'] : null;
$name = trim($_POST['name'] ?? '');

if ($name === '') {
    flash_set('error', 'Design name is required.');
    redirect('/admin/designs.php');
}

/* The type is required and must be one we offer. An image file carries no
   statement about what it depicts, so this is the structured answer a reviewer
   judges the artwork against - and a value from outside the list is refused
   rather than written through. */
$categories = ['Floral', 'Geometric', 'Minimal', 'Pattern', 'Illustration', 'Seasonal'];
$category = trim((string)($_POST['category'] ?? ''));
if (!in_array($category, $categories, true)) {
    // An existing row being renamed keeps whatever it already had.
    $keep = null;
    if ($id) {
        $cs = db()->prepare('SELECT category FROM designs WHERE design_id = :id');
        $cs->execute(['id' => $id]);
        $keep = $cs->fetchColumn() ?: null;
    }
    if ($keep === null) {
        flash_set('error', 'Choose a design type.');
        redirect('/admin/designs.php');
    }
    $category = $keep;
}

$imagePath = null;
$scanned = false;
if (!empty($_FILES['image']['name'])) {
    try {
        $imagePath = handle_image_upload($_FILES['image'], __DIR__ . '/../uploads/designs', $scanned);
    } catch (RuntimeException $e) {
        flash_set('error', $e->getMessage());
        redirect('/admin/designs.php');
    }
} elseif (!$id) {
    flash_set('error', 'A design image is required.');
    redirect('/admin/designs.php');
}

$uploader = (int)current_user()['user_id'];
$pdo = db();

try {
    $pdo->beginTransaction();

    if ($id) {
        if ($imagePath) {
            // A new picture has not been reviewed, whatever the old one's
            // status was.
            $pdo->prepare(
                'UPDATE designs
                    SET name = :name, category = :category, image_path = :img, status = \'pending\',
                        uploaded_by = :uploader, virus_scanned = :scanned,
                        reviewed_by = NULL, reviewed_at = NULL, review_note = NULL
                  WHERE design_id = :id'
            )->execute([
                'name' => $name, 'category' => $category,
                'img' => $imagePath, 'uploader' => $uploader,
                'scanned' => $scanned ? 1 : 0, 'id' => $id,
            ]);
            $message = 'Design image replaced — it is pending review.';
            $action = 'design.updated';
        } else {
            $pdo->prepare('UPDATE designs SET name = :name, category = :category WHERE design_id = :id')
                ->execute(['name' => $name, 'category' => $category, 'id' => $id]);
            $message = 'Design renamed.';
            $action = 'design.updated';
        }
        $entityId = $id;
    } else {
        $pdo->prepare(
            'INSERT INTO designs (name, category, image_path, status, uploaded_by, virus_scanned)
             VALUES (:name, :category, :img, \'pending\', :uploader, :scanned)'
        )->execute([
            'name' => $name, 'category' => $category, 'img' => $imagePath,
            'uploader' => $uploader, 'scanned' => $scanned ? 1 : 0,
        ]);
        $entityId = (int)$pdo->lastInsertId();
        $message = 'Design uploaded — it is pending review and is not visible to customers yet.';
        $action = 'design.uploaded';
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // The row was not written, so the file it would have pointed at is an
    // orphan. Remove it rather than leaving it in the uploads directory.
    if ($imagePath) {
        @unlink(__DIR__ . '/../uploads/designs/' . $imagePath);
    }
    error_log('design_save: ' . $e->getMessage());
    flash_set('error', 'Could not save the design. Please try again.');
    redirect('/admin/designs.php');
}

log_activity(
    $action,
    $name . ($imagePath ? ' (image ' . ($scanned ? 'scanned' : 'not scanned') . ')' : ''),
    'design',
    $entityId
);

flash_set('success', $message);
redirect('/admin/designs.php');
