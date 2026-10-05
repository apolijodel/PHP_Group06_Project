<?php
/**
 * Audit logs — administrator only.
 *
 * The full trail behind the dashboard's Audit trail carousel: who did what,
 * in which module, from which address, and when. Filterable by user, role,
 * action, module and date range, and paged so a long history stays readable.
 *
 * Staff are deliberately excluded. They can see their own work reflected on
 * the dashboard, but the security record of everyone's actions is an
 * administrator's view.
 */
require_once __DIR__ . '/../includes/auth.php';
require_can('security.manage');

$requireCapability = 'security.manage';
$pageTitle = 'Audit Logs';
require __DIR__ . '/includes/admin_header.php';

/* -------------------------------------------------------------- filters */
/**
 * WHO is one control, not two.
 *
 * It used to be a free-text "user" box beside a "role" menu. Typing a name
 * that did not match actor_name exactly returned nothing, which reads as the
 * filter being broken, and picking a role could never narrow to one person.
 * One menu now offers both: "role:<x>" for a whole role and "user:<id>" for
 * an individual, built from the accounts that actually appear in the log, so
 * it can never offer a choice that returns nothing.
 */
$people = db()->query(
    'SELECT user_id, MAX(actor_name) AS actor_name, MAX(actor_role) AS actor_role
       FROM activity_log
      WHERE user_id IS NOT NULL
      GROUP BY user_id
      ORDER BY MAX(actor_role), MAX(actor_name)'
)->fetchAll();

$roleLabels = [
    'admin' => 'All administrators',
    'staff' => 'All staff',
    'customer' => 'All customers',
    'guest' => 'Signed-out visitors',
];

/* Existing links still arrive with ?user=<name> or ?role=<x> (the account
   page links that way). Translate them into the new value so those links keep
   working and the menu opens showing the right option. */
$who = trim((string)($_GET['who'] ?? ''));
if ($who === '') {
    $legacyRole = trim((string)($_GET['role'] ?? ''));
    $legacyUser = trim((string)($_GET['user'] ?? ''));
    if ($legacyUser !== '') {
        foreach ($people as $person) {
            if (strcasecmp((string)$person['actor_name'], $legacyUser) === 0) {
                $who = 'user:' . (int)$person['user_id'];
                break;
            }
        }
    } elseif (isset($roleLabels[$legacyRole])) {
        $who = 'role:' . $legacyRole;
    }
}

$fRole = '';
$fUserId = 0;
if (str_starts_with($who, 'role:') && isset($roleLabels[substr($who, 5)])) {
    $fRole = substr($who, 5);
} elseif (str_starts_with($who, 'user:')) {
    $candidate = (int)substr($who, 5);
    foreach ($people as $person) {
        if ((int)$person['user_id'] === $candidate) {
            $fUserId = $candidate;
            break;
        }
    }
}
// Anything that did not resolve is dropped, so the menu never shows a
// selection the query is not actually applying.
$who = $fRole !== '' ? 'role:' . $fRole : ($fUserId ? 'user:' . $fUserId : '');

$fModule = trim((string)($_GET['module'] ?? ''));
if (!in_array($fModule, activity_modules(), true)) {
    $fModule = '';
}

/**
 * ACTION is the verb, not the whole key.
 *
 * The menu used to list raw keys such as "product.update", so there was no
 * way to ask for "everything that was updated". The verb is the half people
 * mean; it is expanded back into the exact keys below, which keeps the query
 * on the index over action rather than running a function across the column.
 */
$allActions = db()->query('SELECT DISTINCT action FROM activity_log ORDER BY action')
    ->fetchAll(PDO::FETCH_COLUMN);

$verbs = [];
foreach ($allActions as $a) {
    $verb = str_contains($a, '.') ? substr($a, strpos($a, '.') + 1) : $a;
    $verbs[$verb][] = $a;
}
ksort($verbs);

$fAction = trim((string)($_GET['action'] ?? ''));
if (!isset($verbs[$fAction])) {
    $fAction = '';
}

/** Only a real calendar date counts; anything else is dropped, not guessed. */
$asDate = static function (string $value): string {
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return ($d && $d->format('Y-m-d') === $value) ? $value : '';
};
$fFrom = $asDate((string)($_GET['from'] ?? ''));
$fTo = $asDate((string)($_GET['to'] ?? ''));
if ($fFrom !== '' && $fTo !== '' && $fFrom > $fTo) {
    [$fFrom, $fTo] = [$fTo, $fFrom];
}

/* ---------------------------------------------------------- the query */
// One WHERE, built once and used by both the count and the page, so the
// number in the heading can never describe a different set from the rows
// below it. Empty filters add no condition at all.
$where = [];
$params = [];

if ($fRole !== '') {
    $where[] = 'actor_role = :role';
    $params['role'] = $fRole;
}
if ($fUserId) {
    $where[] = 'user_id = :uid';
    $params['uid'] = $fUserId;
}
if ($fModule !== '') {
    $where[] = 'module = :module';
    $params['module'] = $fModule;
}
if ($fAction !== '') {
    $slots = [];
    foreach ($verbs[$fAction] as $i => $key) {
        $slots[] = ':act' . $i;
        $params['act' . $i] = $key;
    }
    $where[] = 'action IN (' . implode(', ', $slots) . ')';
}
if ($fFrom !== '') {
    // From midnight of the chosen day...
    $where[] = 'created_at >= :from';
    $params['from'] = $fFrom . ' 00:00:00';
}
if ($fTo !== '') {
    // ...through its last second, so "to today" includes today.
    $where[] = 'created_at <= :to';
    $params['to'] = $fTo . ' 23:59:59';
}

$sqlWhere = $where ? ' WHERE ' . implode(' AND ', $where) : '';

/* ----------------------------------------------------------------- page */
// Ten at a time. A security log is read a page at a time, and loading
// hundreds of rows to show ten of them is work nobody asked for.
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));

$countStmt = db()->prepare('SELECT COUNT(*) FROM activity_log' . $sqlWhere);
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

// Paging follows the filtered set: asking for page 9 of a two-page result
// lands on the last page rather than an empty one. The filter form itself
// carries no page field, so changing a filter always starts at page 1.
$pages = max(1, (int)ceil($total / $perPage));
$page = min($page, $pages);
$offset = ($page - 1) * $perPage;

$stmt = db()->prepare(
    'SELECT * FROM activity_log' . $sqlWhere
    . ' ORDER BY created_at DESC, log_id DESC LIMIT :lim OFFSET :off'
);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue('lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue('off', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

/** Rebuild the query string while changing one key - used by the pager. */
$pageUrl = static function (int $n) use ($who, $fModule, $fAction, $fFrom, $fTo): string {
    $qs = array_filter([
        'who' => $who, 'module' => $fModule, 'action' => $fAction,
        'from' => $fFrom, 'to' => $fTo, 'page' => $n > 1 ? (string)$n : '',
    ], static fn($v) => $v !== '' && $v !== null);

    return BASE_URL . '/admin/audit_logs.php' . ($qs ? '?' . http_build_query($qs) : '');
};

$anyFilter = $who !== '' || $fModule !== '' || $fAction !== ''
    || $fFrom !== '' || $fTo !== '';
?>

<div class="page-head">
    <div>
        <p class="eyebrow eyebrow-muted">Security</p>
        <h2 style="margin-top:var(--s-2)">Audit logs</h2>
        <p><?= $total ?> <?= $total === 1 ? 'event' : 'events' ?>
            <?= $anyFilter ? 'match these filters' : 'recorded' ?> &middot; newest first</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/security.php" class="btn btn-secondary">
        <?= icon('shield', 16) ?> Security overview
    </a>
</div>

<?php /* A plain GET form. Every filter is applied by the SQL above, never by
         script over the ten rows on screen, and the filtered view is a URL
         you can bookmark or send to someone. There is deliberately no page
         field here: submitting the form always returns to page 1. */ ?>
<form method="get" class="audit-filters" style="border-bottom:0;padding-bottom:0;margin-bottom:var(--s-5)">
    <div class="af-field">
        <label class="form-label" for="alWho">Who</label>
        <select id="alWho" name="who" class="form-select form-select-sm">
            <option value="">Everyone</option>
            <optgroup label="By role">
                <?php foreach ($roleLabels as $value => $label): ?>
                    <option value="role:<?= e($value) ?>"
                        <?= $who === 'role:' . $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </optgroup>
            <?php if ($people): ?>
                <optgroup label="By person">
                    <?php foreach ($people as $person): ?>
                        <option value="user:<?= (int)$person['user_id'] ?>"
                            <?= $who === 'user:' . (int)$person['user_id'] ? 'selected' : '' ?>>
                            <?= e($person['actor_name']) ?> &middot; <?= e(role_label($person['actor_role'])) ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endif; ?>
        </select>
    </div>
    <div class="af-field">
        <label class="form-label" for="alAction">Action</label>
        <select id="alAction" name="action" class="form-select form-select-sm">
            <option value="">All actions</option>
            <?php foreach ($verbs as $verb => $keys): ?>
                <option value="<?= e($verb) ?>" <?= $fAction === $verb ? 'selected' : '' ?>>
                    <?= e(ucfirst(str_replace(['_', '.'], ' ', $verb))) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="af-field">
        <label class="form-label" for="alModule">Module</label>
        <select id="alModule" name="module" class="form-select form-select-sm">
            <option value="">All modules</option>
            <?php foreach (activity_modules() as $m): ?>
                <option value="<?= e($m) ?>" <?= $fModule === $m ? 'selected' : '' ?>><?= e($m) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="af-field">
        <label class="form-label" for="alFrom">From</label>
        <input type="date" id="alFrom" name="from" class="form-control form-control-sm"
               value="<?= e($fFrom) ?>" max="<?= e(date('Y-m-d')) ?>">
    </div>
    <div class="af-field">
        <label class="form-label" for="alTo">To</label>
        <input type="date" id="alTo" name="to" class="form-control form-control-sm"
               value="<?= e($fTo) ?>" max="<?= e(date('Y-m-d')) ?>">
    </div>
    <div class="af-actions">
        <button type="submit" class="btn btn-primary btn-sm">
            <?= icon('filter', 15) ?> Apply filters
        </button>
        <?php /* Always here, so the bar does not reflow the moment a filter is
                 set and the button is never somewhere different from where you
                 last saw it. With nothing to clear it is disabled rather than
                 absent - the same pattern the pager below uses. */ ?>
        <a href="<?= BASE_URL ?>/admin/audit_logs.php"
           class="btn btn-secondary btn-sm <?= $anyFilter ? '' : 'disabled' ?>"
           <?= $anyFilter ? '' : 'aria-disabled="true" tabindex="-1"' ?>>
            <?= icon('close', 14) ?> Clear filters
        </a>
    </div>
</form>

<div class="data-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <caption class="visually-hidden">Audit log entries</caption>
            <thead>
                <tr>
                    <th scope="col">Date/Time</th>
                    <th scope="col">User</th>
                    <th scope="col">Role</th>
                    <th scope="col">Action</th>
                    <th scope="col">Module</th>
                    <th scope="col">Description</th>
                    <th scope="col">IP address</th>
                    <th scope="col">Device</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td class="text-muted" style="font-size:.82rem;white-space:nowrap">
                            <?= e(date('M j, Y', strtotime((string)$row['created_at']))) ?><br>
                            <?= e(date('g:i:s A', strtotime((string)$row['created_at']))) ?>
                        </td>
                        <th scope="row" style="font-weight:600">
                            <?php if ($row['user_id']): ?>
                                <?php /* Every role is viewable, not just customers - an administrator
                                         auditing a staff action needs to see whose account it was.
                                         user_view.php shows profile and activity only: no password
                                         hash, no MFA secret, no tokens. */ ?>
                                <a href="<?= BASE_URL ?>/admin/user_view.php?id=<?= (int)$row['user_id'] ?>">
                                    <?= e($row['actor_name']) ?>
                                </a>
                            <?php else: ?>
                                <?= e($row['actor_name']) ?>
                            <?php endif; ?>
                        </th>
                        <td>
                            <span class="badge badge-role r-<?= e($row['actor_role']) ?>">
                                <?= e(role_label($row['actor_role'])) ?>
                            </span>
                        </td>
                        <td class="mono-num" style="font-size:.8rem"><?= e($row['action']) ?></td>
                        <td style="font-size:.82rem"><?= e((string)($row['module'] ?? '—')) ?></td>
                        <td style="font-size:.85rem"><?= e($row['summary']) ?></td>
                        <td class="mono-num" style="font-size:.78rem"><?= e((string)($row['ip_address'] ?? '—')) ?></td>
                        <?php /* Derived from the User-Agent, which is self-reported and can
                                 be anything the caller likes - useful for reading the log,
                                 never evidence. The raw string is the title so it can be
                                 checked, and nothing here exposes a credential. */ ?>
                        <?php $agent = user_agent_summary($row['user_agent'] ?? null); ?>
                        <td style="font-size:.78rem;white-space:nowrap"
                            title="<?= e((string)($row['user_agent'] ?? 'No user agent recorded')) ?>">
                            <span class="ua-line"><?= e($agent['device']) ?></span>
                            <span class="ua-sub"><?= e($agent['os']) ?> &middot; <?= e($agent['browser']) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <span class="empty-icon"><?= icon('list', 24) ?></span>
                                <h3><?= $anyFilter ? 'Nothing matches those filters' : 'No events recorded yet' ?></h3>
                                <p><?= $anyFilter
                                    ? 'Try a wider date range, or clear a filter.'
                                    : 'Sign-ins, orders and catalog changes are recorded here as they happen.' ?></p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($pages > 1): ?>
    <nav class="d-flex align-items-center justify-content-between" style="margin-top:var(--s-5)"
         aria-label="Audit log pages">
        <span class="text-muted" style="font-size:.85rem">
            Showing <?= $total ? $offset + 1 : 0 ?>&ndash;<?= min($offset + $perPage, $total) ?>
            of <?= $total ?> &middot; page <?= $page ?> of <?= $pages ?>
        </span>
        <div class="d-flex gap-2">
            <a class="btn btn-secondary btn-sm <?= $page <= 1 ? 'disabled' : '' ?>"
               href="<?= e($pageUrl(max(1, $page - 1))) ?>"
               <?= $page <= 1 ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
                <?= icon('chevron-left', 14) ?> Newer
            </a>
            <a class="btn btn-secondary btn-sm <?= $page >= $pages ? 'disabled' : '' ?>"
               href="<?= e($pageUrl(min($pages, $page + 1))) ?>"
               <?= $page >= $pages ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
                Older <?= icon('chevron-right', 14) ?>
            </a>
        </div>
    </nav>
<?php endif; ?>

<?php require __DIR__ . '/../includes/backoffice/shell_footer.php'; ?>
