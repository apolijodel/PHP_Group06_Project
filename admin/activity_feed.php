<?php
/**
 * One batch of audit-trail cards.
 *
 * The dashboard renders the first five itself; this hands back the next five
 * whenever the carousel runs out of cards to page through. It returns the same
 * card partial the dashboard uses, so the markup cannot drift between them.
 *
 * Capability is checked here in its own right. The carousel calling it is a
 * convenience, never the authorisation.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/icons.php';
require_manager();
require_can('dashboard.view');

$limit = 5;
$offset = max(0, (int)($_GET['offset'] ?? 0));

// A hard ceiling: paging this far back is a job for a report, not a carousel.
if ($offset > 500) {
    http_response_code(204);
    exit;
}

// The same helper the dashboard uses, so a later batch can never be filtered
// differently from the first one.
$filters = activity_filters($_GET);

$stmt = db()->prepare(
    'SELECT * FROM activity_log' . $filters['sql']
    . ' ORDER BY created_at DESC, log_id DESC LIMIT :lim OFFSET :off'
);
foreach ($filters['params'] as $key => $value) {
    $stmt->bindValue($key, $value);
}
// LIMIT/OFFSET have to bind as integers; with emulation off they would
// otherwise be sent as quoted strings and the statement would fail.
$stmt->bindValue('lim', $limit, PDO::PARAM_INT);
$stmt->bindValue('off', $offset, PDO::PARAM_INT);
$stmt->execute();
$events = $stmt->fetchAll();

if (!$events) {
    // Nothing left. 204 tells the carousel to stop asking.
    http_response_code(204);
    exit;
}

foreach ($events as $event) {
    require __DIR__ . '/../includes/backoffice/activity_card.php';
}
