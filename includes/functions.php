<?php
require_once __DIR__ . '/../config/db.php';

/**
 * One clock for the whole application.
 *
 * Without this PHP runs on whatever php.ini happens to say while MySQL runs
 * on the machine's timezone. Every comparison between a stored timestamp and
 * PHP's time() is then off by the gap between them - which is how a lockout
 * could never expire and "just now" rendered as "16 hours ago".
 */
if (defined('APP_TIMEZONE') && APP_TIMEZONE !== '') {
    date_default_timezone_set(APP_TIMEZONE);
}

/**
 * Session cookie hardening, applied before the session starts - after
 * session_start() these have no effect.
 *
 *   httponly  keeps the cookie out of reach of JavaScript, so an XSS bug
 *             cannot simply read the session id.
 *   samesite  Lax stops the cookie riding along with cross-site POSTs,
 *             which is defence in depth behind the CSRF tokens.
 *   secure    only over HTTPS. Set FORCE_HTTPS once the site is served
 *             over TLS; on plain localhost it must stay off, or the browser
 *             would refuse to send the cookie at all and nobody could log in.
 */
if (session_status() === PHP_SESSION_NONE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (defined('FORCE_HTTPS') && FORCE_HTTPS);

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('MARKMESESSID');
    session_start();
}

/**
 * Idle session timeout.
 *
 * Runs on every request that loads this file, which is every page and every
 * POST endpoint, so an expired session cannot be used by going straight to a
 * handler URL. The window is a setting rather than a constant; the CS 108
 * baseline is 120 seconds.
 *
 * Only signed-in sessions expire - a guest browsing the catalogue has nothing
 * to time out, and logging them out mid-cart would be noise, not security.
 */
(static function (): void {
    if (!isset($_SESSION['user'])) {
        return;
    }

    // Read straight from the settings table: security.php is not loaded on
    // every request, and this must not depend on the caller remembering to.
    static $timeout = null;
    if ($timeout === null) {
        try {
            $stmt = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = :k');
            $stmt->execute(['k' => 'session_timeout_secs']);
            $timeout = max(30, (int)($stmt->fetchColumn() ?: 120));
        } catch (Throwable $e) {
            $timeout = 120;
        }
    }

    $last = $_SESSION['last_activity'] ?? time();

    if (time() - $last > $timeout) {
        $_SESSION = [];
        session_destroy();

        // Start a fresh session purely to carry the explanation across the
        // redirect, so the user is told why rather than silently bounced.
        session_start();
        session_regenerate_id(true);
        // Set on the channels the two front doors already read, so the
        // message appears wherever the user lands rather than needing a new
        // banner on every page.
        $message = 'Your session has expired. Please log in again.';
        $_SESSION['flash']['session_expired'] = $message;
        $_SESSION['flash']['login_error'] = $message;
        $_SESSION['flash']['error'] = $message;
        $_SESSION['flash']['reopen_modal'] = 'login';
        return;
    }

    $_SESSION['last_activity'] = time();
})();

/** Escape for HTML output. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

function format_price(float $amount): string
{
    return '₱' . number_format($amount, 2);
}

function flash_set(string $key, mixed $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function flash_get(string $key): mixed
{
    if (!empty($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

/** Current request path relative to BASE_URL, e.g. "/customer/product.php?id=3". */
function current_path(): string
{
    $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    if ($basePath !== '' && str_starts_with($requestUri, $basePath)) {
        $requestUri = substr($requestUri, strlen($basePath));
    }
    return $requestUri === '' ? '/' : $requestUri;
}

/** Only allow redirecting back to a local app path — never an absolute/external URL. */
function safe_local_path(?string $path, string $default = '/customer/index.php'): string
{
    if (!$path || $path[0] !== '/' || str_starts_with($path, '//') || str_contains($path, '\\')) {
        return $default;
    }
    return $path;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(400);
        die('Invalid or expired form submission. Please go back and try again.');
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function is_admin(): bool
{
    return is_logged_in() && $_SESSION['user']['role'] === 'admin';
}

/** A restricted management account: the back office, minus the dangerous parts. */
function is_staff(): bool
{
    return is_logged_in() && $_SESSION['user']['role'] === 'staff';
}

/** Anyone allowed into /admin at all — administrator or staff. */
function is_manager(): bool
{
    return is_admin() || is_staff();
}

/**
 * The one place a role's permissions are defined.
 *
 * Staff is an allowlist, not a subtraction: anything not named here is
 * administrator-only, so a capability added later is denied to staff by
 * default rather than silently granted. Every admin screen and every admin
 * POST endpoint checks through this, so the sidebar, the buttons and the
 * server can never disagree about what staff may do.
 */
function role_capabilities(string $role): array
{
    if ($role === 'admin') {
        return ['*'];
    }

    if ($role === 'staff') {
        return [
            'dashboard.view',
            'products.view',
            'products.restock',
            'inventory.view',
            'orders.view',
            'orders.status',
            'customers.view',
            'profile.self',
            // Their own theme, not the shop's configuration: 'settings.manage'
            // stays with administrators and still gates the store and system
            // panels on /admin/settings.php.
            'settings.appearance',
        ];
    }

    return [];
}

/** True when the signed-in user holds $capability. */
function can(string $capability): bool
{
    if (!is_logged_in()) {
        return false;
    }

    $caps = role_capabilities($_SESSION['user']['role']);

    return in_array('*', $caps, true) || in_array($capability, $caps, true);
}

/** Human label for a management role, used in the admin chrome. */
/**
 * Which back-office panel the signed-in user belongs to.
 *
 * Administrators work in /admin, staff in /staff. The two panels render the
 * same screens from includes/views, so this is what keeps a staff member's
 * links inside their own panel instead of bouncing them into the admin one.
 */
function panel_base(): string
{
    return is_admin() ? '/admin' : '/staff';
}

/** A URL inside the signed-in user's own panel. */
function panel_url(string $page = ''): string
{
    return BASE_URL . panel_base() . ($page !== '' ? '/' . ltrim($page, '/') : '');
}

function role_label(?string $role = null): string
{
    $role = $role ?? ($_SESSION['user']['role'] ?? '');

    return ['admin' => 'Administrator', 'staff' => 'Staff', 'customer' => 'Customer'][$role] ?? 'User';
}

function get_or_create_cart_id(int $userId): int
{
    $stmt = db()->prepare('SELECT cart_id FROM carts WHERE user_id = :id');
    $stmt->execute(['id' => $userId]);
    $cartId = $stmt->fetchColumn();

    if ($cartId === false) {
        db()->prepare('INSERT INTO carts (user_id) VALUES (:id)')->execute(['id' => $userId]);
        $cartId = db()->lastInsertId();
    }

    return (int)$cartId;
}

/** Total number of items (summed quantities) in the current user's cart. */
function cart_item_count(): int
{
    if (!is_logged_in()) {
        return 0;
    }

    $stmt = db()->prepare(
        'SELECT COALESCE(SUM(ci.quantity), 0)
         FROM cart_items ci
         JOIN carts c ON c.cart_id = ci.cart_id
         WHERE c.user_id = :uid'
    );
    $stmt->execute(['uid' => current_user()['user_id']]);

    return (int)$stmt->fetchColumn();
}

/** Initials for the avatar chip, e.g. "Juana Dela Cruz" -> "JD". */
function initials(?string $name): string
{
    $parts = preg_split('/\s+/', trim((string)$name)) ?: [];
    $parts = array_values(array_filter($parts));

    if (!$parts) {
        return '?';
    }

    $first = mb_substr($parts[0], 0, 1);
    $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';

    return mb_strtoupper($first . $last);
}

/**
 * Stock presentation: [css modifier, label]. Keeps "in stock / low / out"
 * wording identical everywhere it is shown.
 */
function stock_state(int $quantity, int $threshold = 5): array
{
    $threshold = max(1, $threshold);

    if ($quantity <= 0) {
        return ['out', 'Out of stock'];
    }
    if ($quantity <= $threshold) {
        return ['low', 'Only ' . $quantity . ' left'];
    }
    return ['in', 'In stock'];
}

/** The order statuses in workflow order, as defined by the orders.status enum. */
function order_statuses(): array
{
    return ['Pending', 'Processing', 'On Shipping', 'Completed'];
}

/** Every value the orders.status enum accepts, including Cancelled. */
function all_order_statuses(): array
{
    return ['Pending', 'Processing', 'On Shipping', 'Completed', 'Cancelled'];
}

/**
 * The three stages a customer is shown on the order timeline. "Processing"
 * is folded into "Pending" by customer_status(), so it is not a stage here.
 */
function customer_order_flow(): array
{
    return ['Pending', 'On Shipping', 'Completed'];
}

/**
 * What the customer sees. "Processing" is an internal step — for a customer
 * the order simply has not shipped yet — so it is shown as "Pending" and the
 * customer-facing vocabulary stays four plain words. Admin screens always show
 * the real stored value via $order['status'].
 */
function customer_status(string $status): string
{
    return $status === 'Processing' ? 'Pending' : $status;
}

/** Bootstrap contextual colour for an order status badge. */
function status_variant(string $status): string
{
    return [
        'Pending' => 'secondary',
        'Processing' => 'info',
        'On Shipping' => 'warning',
        'Completed' => 'success',
        'Cancelled' => 'danger',
    ][$status] ?? 'secondary';
}

/** Marks the matching navbar entry as current. */
function nav_active(string ...$scripts): string
{
    return in_array(basename($_SERVER['SCRIPT_NAME'] ?? ''), $scripts, true) ? ' active' : '';
}

/**
 * Lowercased keyword used by the CSS preview to pick a bookmark silhouette.
 * Falls back to the default tapered shape for anything unrecognised.
 */
function shape_key(?string $name): string
{
    $n = strtolower((string)$name);

    // Longest/most specific first: "rounded rectangle" must not match the
    // plain "rectangle" rule, and "corner bookmark" must not match "corner"
    // after something else has already claimed it.
    $keys = [
        'rounded rectangle' => 'rounded',
        'classic rectangle' => 'rectangle',
        'corner' => 'corner',
        'rectangular' => 'rectangle',
        'rectangle' => 'rectangle',
        'rounded' => 'rounded',
        'arched' => 'arched',
        'notched' => 'notched',
        'heart' => 'heart',
        'circle' => 'circle',
        'oval' => 'oval',
        'star' => 'star',
        'cloud' => 'cloud',
        'flower' => 'flower',
        'leaf' => 'leaf',
        'cat' => 'cat',
        'ribbon' => 'ribbon',
        'ticket' => 'ticket',
        'hexagon' => 'hexagon',
        'shield' => 'shield',
        'tassel' => 'tassel',
    ];

    foreach ($keys as $needle => $key) {
        if (str_contains($n, $needle)) {
            return $key;
        }
    }

    return 'classic';
}

/**
 * URL for a file under /uploads, with a filemtime cache-buster.
 *
 * Without this, replacing artwork under the same filename leaves browsers
 * serving the old cached image indefinitely — the same reason style.css and
 * script.js are already versioned.
 */
function upload_url(string $folder, ?string $file): string
{
    $file = (string)$file;
    $base = BASE_URL . '/uploads/' . $folder . '/' . rawurlencode($file);
    $path = __DIR__ . '/../uploads/' . $folder . '/' . $file;

    return is_file($path) ? $base . '?v=' . filemtime($path) : $base;
}

/**
 * Store settings, read once per request from the `settings` table.
 * Returns $default when the key has never been saved.
 */
function setting(string $key, string $default = ''): string
{
    static $cache = null;

    if ($cache === null) {
        $cache = [];
        foreach (db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll() as $row) {
            $cache[$row['setting_key']] = (string)$row['setting_value'];
        }
    }

    return $cache[$key] ?? $default;
}

/** Insert-or-update a single setting. */
function setting_put(string $key, string $value): void
{
    db()->prepare(
        'INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    )->execute(['k' => $key, 'v' => $value]);
}

/**
 * URL of a user's profile picture, or null when they have not uploaded one
 * (callers fall back to the initials chip).
 */
function avatar_url(?string $file): ?string
{
    if (!$file) {
        return null;
    }
    return is_file(__DIR__ . '/../uploads/avatars/' . $file)
        ? upload_url('avatars', $file)
        : null;
}

/**
 * True when the request came from our own fetch() calls.
 *
 * The header is set explicitly by assets/js/script.js. It is only used to
 * choose a response *format* — never to decide whether an action is allowed,
 * since a client controls its own headers.
 */
function is_ajax(): bool
{
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
}

/** Send a JSON payload and stop. */
function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload);
    exit;
}

/**
 * The signed-in customer's cart: rows, subtotal and unit count.
 * Shared by the cart page, the checkout summary and the slide-out drawer, so
 * all three always agree.
 */
function cart_contents(): array
{
    if (!is_logged_in()) {
        return ['items' => [], 'subtotal' => 0.0, 'count' => 0];
    }

    $cartId = get_or_create_cart_id(current_user()['user_id']);

    $stmt = db()->prepare(
        'SELECT ci.*, p.name AS product_name, p.price, p.stock_quantity,
                p.image_path AS product_image,
                s.name AS shape_name, d.name AS design_name
         FROM cart_items ci
         JOIN products p ON p.product_id = ci.product_id
         LEFT JOIN shapes s ON s.shape_id = ci.shape_id
         LEFT JOIN designs d ON d.design_id = ci.design_id
         WHERE ci.cart_id = :cart_id
         ORDER BY ci.created_at DESC'
    );
    $stmt->execute(['cart_id' => $cartId]);
    $items = $stmt->fetchAll();

    $subtotal = 0.0;
    $count = 0;
    foreach ($items as $item) {
        $subtotal += (float)$item['price'] * (int)$item['quantity'];
        $count += (int)$item['quantity'];
    }

    return ['items' => $items, 'subtotal' => $subtotal, 'count' => $count];
}

/**
 * A one-line description of a cart/order line's customization,
 * e.g. "Rounded · Floral · “For Mama”". Empty for ready-made products.
 */
function customization_summary(array $row): string
{
    $parts = array_filter([
        $row['shape_name'] ?? $row['shape_name_snapshot'] ?? null,
        $row['design_name'] ?? $row['design_name_snapshot'] ?? null,
        !empty($row['custom_text']) ? '“' . $row['custom_text'] . '”' : null,
        !empty($row['custom_image_path']) ? 'with your photo' : null,
    ]);

    return implode(' · ', $parts);
}

/**
 * Human-readable order reference, e.g. "MM20260925-0002".
 *
 * Display only — orders are still keyed by orders.order_id everywhere, and
 * every link and lookup uses that. This just gives the customer something
 * quotable that carries the order date, without adding a column.
 */
function order_reference(array $order): string
{
    $date = date('Ymd', strtotime((string)$order['created_at']));

    return 'MM' . $date . '-' . str_pad((string)(int)$order['order_id'], 4, '0', STR_PAD_LEFT);
}

/**
 * The customer's delivery address as one line, composed from its parts.
 *
 * The parts are stored separately so the province/city dropdowns can be
 * repopulated exactly; this is what gets copied into orders.delivery_address
 * at checkout, so an order still records a single readable address.
 */
function full_address(array $user): string
{
    $parts = array_filter([
        $user['address'] ?? null,
        $user['city'] ?? null,
        $user['province'] ?? null,
        $user['postal_code'] ?? null,
    ], static fn($part) => $part !== null && trim((string)$part) !== '');

    return implode(', ', $parts);
}


/* ===================================================================
   Audit trail
   =================================================================== */

/**
 * Record one notable event.
 *
 * Deliberately best-effort: an audit trail exists to describe what the site
 * did, and it must never be the reason the site failed to do it. A missing
 * table or a full disk loses the entry, not the customer's order - so every
 * failure here is swallowed rather than thrown.
 *
 * The actor's name and role are copied onto the row rather than joined at
 * read time, because the history has to stay readable after an account is
 * renamed or deleted.
 *
 * @param string $action  machine key, e.g. 'order.placed'
 * @param string $summary one human sentence, already in past tense
 */
function log_activity(
    string $action,
    string $summary,
    ?string $entityType = null,
    ?int $entityId = null,
    ?array $actor = null,
    ?string $module = null
): void {
    try {
        $actor ??= current_user();

        $role = $actor['role'] ?? 'guest';
        if (!in_array($role, ['customer', 'staff', 'admin'], true)) {
            $role = 'guest';
        }

        $name = trim((string)($actor['username'] ?? $actor['full_name'] ?? ''));
        if ($name === '') {
            $name = 'Guest';
        }

        db()->prepare(
            'INSERT INTO activity_log
                (user_id, actor_role, actor_name, action, module, entity_type, entity_id,
                 summary, ip_address, user_agent)
             VALUES (:uid, :role, :name, :action, :module, :etype, :eid,
                     :summary, :ip, :ua)'
        )->execute([
            'uid' => isset($actor['user_id']) ? (int)$actor['user_id'] : null,
            'role' => $role,
            'name' => mb_substr($name, 0, 100),
            'action' => mb_substr($action, 0, 50),
            'module' => mb_substr($module ?? activity_module($action), 0, 40),
            'etype' => $entityType !== null ? mb_substr($entityType, 0, 30) : null,
            'eid' => $entityId,
            'summary' => mb_substr($summary, 0, 255),
            // Where the action came from. Proxy headers are ignored on
            // purpose: they are set by the caller and would let anyone write
            // whatever address they liked into the audit trail.
            'ip' => mb_substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45) ?: null,
            'ua' => mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255) ?: null,
        ]);
    } catch (Throwable $e) {
        // Intentionally silent. See the note above.
    }
}

/**
 * Which part of the site an action belongs to, derived from its prefix so a
 * caller that forgets to name a module still files the entry somewhere sane.
 */
function activity_module(string $action): string
{
    return match (strtok($action, '.')) {
        'auth' => 'Authentication',
        'account' => 'Account',
        'order' => 'Order Management',
        'product' => 'Product Management',
        'inventory' => 'Inventory',
        'category' => 'Category Management',
        'shape', 'design' => 'Customization Options',
        'customer' => 'Customer Management',
        'staff', 'admin' => 'User Management',
        'settings', 'security' => 'Security',
        default => 'General',
    };
}

/** The modules that appear in the audit-log filter, in menu order. */
function activity_modules(): array
{
    return [
        'Authentication',
        'Account',
        'Order Management',
        'Product Management',
        'Inventory',
        'Category Management',
        'Customization Options',
        'Customer Management',
        'User Management',
        'Security',
        'General',
    ];
}

/**
 * Read the audit-trail filters off a query string and turn them into a WHERE
 * fragment plus bound parameters.
 *
 * Lives here rather than in either page because the dashboard renders the
 * first batch and activity_feed.php renders every batch after it; if they
 * built the filter separately, paging would quietly widen it.
 *
 * Anything unparseable is dropped rather than guessed at - a filter that
 * silently means something other than what it says is worse than no filter.
 *
 * @return array{role: ?string, from: ?string, to: ?string, sql: string, params: array, active: bool}
 */
function activity_filters(array $input): array
{
    $role = $input['role'] ?? '';
    if (!in_array($role, ['customer', 'staff', 'admin'], true)) {
        $role = null;
    }

    /** A date only counts if it is a real calendar date in Y-m-d form. */
    $asDate = static function ($value): ?string {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }
        $d = DateTime::createFromFormat('Y-m-d', $value);
        return ($d && $d->format('Y-m-d') === $value) ? $value : null;
    };

    $from = $asDate($input['from'] ?? '');
    $to = $asDate($input['to'] ?? '');

    // Back-to-front dates are a slip, not a request for zero rows.
    if ($from && $to && $from > $to) {
        [$from, $to] = [$to, $from];
    }

    $where = [];
    $params = [];

    if ($role !== null) {
        $where[] = 'actor_role = :f_role';
        $params['f_role'] = $role;
    }
    if ($from !== null) {
        $where[] = 'created_at >= :f_from';
        $params['f_from'] = $from . ' 00:00:00';
    }
    if ($to !== null) {
        // Inclusive of the whole closing day, and still comparing against the
        // raw column so the created_at index stays usable.
        $where[] = 'created_at <= :f_to';
        $params['f_to'] = $to . ' 23:59:59';
    }

    return [
        'role' => $role,
        'from' => $from,
        'to' => $to,
        'sql' => $where ? ' WHERE ' . implode(' AND ', $where) : '',
        'params' => $params,
        'active' => (bool)$where,
    ];
}

/** The filters as a query string, for links and for the carousel's paging. */
function activity_filter_query(array $filters, array $extra = []): string
{
    $parts = array_filter([
        'role' => $filters['role'],
        'from' => $filters['from'],
        'to' => $filters['to'],
    ] + $extra, static fn($v) => $v !== null && $v !== '');

    return $parts ? http_build_query($parts) : '';
}

/**
 * The icon and badge tone for an event, chosen from its action prefix so a
 * new action never renders without one.
 */
function activity_style(string $action): array
{
    $group = strtok($action, '.');

    return match ($group) {
        'order' => ['receipt', 'accent'],
        'product', 'inventory' => ['package', 'green'],
        'category', 'shape', 'design' => ['tag', 'green'],
        'account', 'auth' => ['user', 'neutral'],
        'customer', 'staff' => ['users', 'neutral'],
        'settings' => ['settings', 'neutral'],
        default => ['info', 'neutral'],
    };
}

/**
 * "just now", "12 minutes ago", "3 days ago" - a log is read as a sequence of
 * recent things, and an absolute timestamp makes that harder, not easier. The
 * exact time still goes in the title attribute.
 */
function time_ago(string $timestamp): string
{
    $then = strtotime($timestamp);
    $diff = max(0, time() - $then);

    if ($diff < 60) {
        return 'just now';
    }

    foreach ([
        [31536000, 'year'],
        [2592000, 'month'],
        [604800, 'week'],
        [86400, 'day'],
        [3600, 'hour'],
        [60, 'minute'],
    ] as [$seconds, $unit]) {
        if ($diff >= $seconds) {
            $n = (int)floor($diff / $seconds);
            return $n . ' ' . $unit . ($n === 1 ? '' : 's') . ' ago';
        }
    }

    return 'just now';
}
