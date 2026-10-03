<?php
/**
 * Approve or reject an uploaded shape or design.
 *
 * POST only, CSRF checked, and the capability is required here in its own
 * right — this endpoint is reachable directly, so the review page hiding a
 * button is not what stops anyone. options.manage is held by administrators
 * only, which is what keeps staff and customers out of it.
 *
 * Approving does not touch any other row: an approved option that is already
 * live is never altered by reviewing a different submission.
 */
require_once __DIR__ . '/../includes/auth.php';
require_can('options.manage');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/option_review.php');
}
csrf_check();

/* The table is chosen from a fixed map, never from the request, so the kind
   cannot be steered into some other table. */
$map = [
    'shape' => ['shapes', 'shape_id'],
    'design' => ['designs', 'design_id'],
];
$kind = (string)($_POST['kind'] ?? '');
if (!isset($map[$kind])) {
    flash_set('error', 'Unknown item type.');
    redirect('/admin/option_review.php');
}
[$table, $key] = $map[$kind];

$id = (int)($_POST['id'] ?? 0);
$decision = (string)($_POST['decision'] ?? '');
if (!in_array($decision, ['approve', 'reject'], true)) {
    flash_set('error', 'Unknown decision.');
    redirect('/admin/option_review.php');
}

$note = trim((string)($_POST['note'] ?? ''));
$note = $note === '' ? null : mb_substr($note, 0, 255);

$stmt = db()->prepare("SELECT name, status FROM {$table} WHERE {$key} = :id");
$stmt->execute(['id' => $id]);
$item = $stmt->fetch();

if (!$item) {
    flash_set('error', 'That item no longer exists.');
    redirect('/admin/option_review.php');
}

$status = $decision === 'approve' ? 'approved' : 'rejected';

try {
    db()->prepare(
        "UPDATE {$table}
            SET status = :status, reviewed_by = :reviewer,
                reviewed_at = CURRENT_TIMESTAMP, review_note = :note
          WHERE {$key} = :id"
    )->execute([
        'status' => $status,
        'reviewer' => (int)current_user()['user_id'],
        'note' => $decision === 'reject' ? $note : null,
        'id' => $id,
    ]);
} catch (Throwable $e) {
    error_log('option_review_action: ' . $e->getMessage());
    flash_set('error', 'Could not record that decision. Please try again.');
    redirect('/admin/option_review.php');
}

log_activity(
    $kind . '.' . $status,
    ucfirst($kind) . ' "' . $item['name'] . '" ' . $status
        . ($note ? ' — ' . $note : ''),
    $kind,
    $id
);

flash_set(
    'success',
    ucfirst($kind) . ' "' . $item['name'] . '" ' . $status . '.'
    . ($status === 'approved' ? ' It is now available in the design studio.' : '')
);
redirect('/admin/option_review.php?status=' . ($decision === 'approve' ? 'pending' : 'pending'));
