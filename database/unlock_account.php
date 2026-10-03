<?php
/**
 * Command-line account unlock — the recovery path when nobody can sign in.
 *
 * Usage, from the project root:
 *     php database/unlock_account.php admin
 *     php database/unlock_account.php --list
 *
 * Why this exists: the web unlock screen needs an administrator, so if the
 * only administrator locks themselves out there is nobody left with
 * permission to let them back in. Lockouts also expire on their own (see
 * lockout_minutes in Security settings), but waiting is not a recovery plan
 * when you need in now.
 *
 * It refuses to run over HTTP. Anything reachable from a browser that clears
 * lockouts would undo the lockout entirely.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script only runs from the command line.\n");
}

require_once __DIR__ . '/../includes/functions.php';

$arg = $argv[1] ?? '';

if ($arg === '' || $arg === '--help' || $arg === '-h') {
    echo "Unlock a MarkMe account that has been locked by failed sign-ins.\n\n";
    echo "  php database/unlock_account.php <username>   unlock that account\n";
    echo "  php database/unlock_account.php --list       show locked accounts\n";
    exit(0);
}

if ($arg === '--list') {
    $rows = db()->query(
        'SELECT l.username, u.role, l.failed_attempts, l.locked_at
           FROM account_locks l JOIN users u ON u.user_id = l.user_id
          WHERE l.locked_at IS NOT NULL
          ORDER BY l.locked_at DESC'
    )->fetchAll();

    if (!$rows) {
        exit("No accounts are locked.\n");
    }

    printf("%-20s %-14s %-9s %s\n", 'USERNAME', 'ROLE', 'ATTEMPTS', 'LOCKED AT');
    foreach ($rows as $r) {
        printf("%-20s %-14s %-9d %s\n",
            $r['username'], $r['role'], $r['failed_attempts'], $r['locked_at']);
    }
    exit(0);
}

$stmt = db()->prepare('SELECT user_id, role FROM users WHERE username = :u OR email = :e');
$stmt->execute(['u' => $arg, 'e' => $arg]);
$user = $stmt->fetch();

if (!$user) {
    exit("No account found for \"$arg\".\n");
}

$del = db()->prepare('DELETE FROM account_locks WHERE user_id = :id');
$del->execute(['id' => (int)$user['user_id']]);

if ($del->rowCount() === 0) {
    exit("\"$arg\" was not locked. Nothing to do.\n");
}

// Recorded like any other unlock, so the audit trail shows how it happened.
log_activity(
    'account.unlocked',
    'Unlocked ' . $arg . ' from the command line',
    'user',
    (int)$user['user_id'],
    ['user_id' => null, 'role' => 'guest', 'username' => 'CLI'],
    'Security'
);

echo "Unlocked \"$arg\" (" . $user['role'] . "). Failed attempts reset to zero.\n";
