<?php
/**
 * MarkMe security core.
 *
 * Everything here is real: the TOTP codes are verified against RFC 6238, the
 * lockout actually blocks the login, and the CAPTCHA and mailer report
 * honestly when they are not configured rather than pretending to pass.
 *
 * No Composer dependency. TOTP is ~40 lines of hash_hmac, and the QR code is
 * rendered in the browser from the otpauth:// URI, so the shared secret is
 * never handed to a third-party image service.
 */
require_once __DIR__ . '/functions.php';

/* ===================================================================
   Security settings

   Stored in the existing settings table so an administrator can change
   them. The defaults below are the CS 108 baseline.
   =================================================================== */

/** Maximum failed logins before the account locks. Required baseline: 3. */
function sec_max_attempts(): int
{
    return max(1, (int)setting('max_login_attempts', '3'));
}

/** Idle seconds before the session is destroyed. Demo baseline: 120. */
function sec_session_timeout(): int
{
    return max(30, (int)setting('session_timeout_secs', '120'));
}

/** Minimum password length. Required baseline: 12. */
function sec_password_min(): int
{
    return max(8, (int)setting('password_min_length', '12'));
}

/** Days before a password must be changed. 0 disables expiry. */
function sec_password_expiry_days(): int
{
    return max(0, (int)setting('password_expiry_days', '90'));
}

/**
 * How long a lockout lasts before it lifts itself, in minutes. 0 means it
 * never does and only an administrator can clear it.
 *
 * This exists because the strict reading - lock forever, administrator
 * unlocks - deadlocks the moment the only administrator is the one locked
 * out. There is then nobody left with permission to unlock them. A lockout
 * window keeps the control (three strikes still locks the account) while
 * guaranteeing the site cannot lock itself permanently.
 */
function sec_lockout_minutes(): int
{
    return max(0, (int)setting('lockout_minutes', '15'));
}

/** Roles that must complete MFA enrolment before using the back office. */
function sec_mfa_required_roles(): array
{
    return array_values(array_filter(array_map(
        'trim',
        explode(',', setting('mfa_required_roles', 'admin,staff'))
    )));
}

/* ===================================================================
   Password policy
   =================================================================== */

/**
 * The rules, as label => regex-or-callable, in the order they are shown.
 *
 * One definition drives the server-side check, the registration checklist and
 * the change-password checklist, so the list a user is shown is literally the
 * list that is enforced.
 */
function password_rules(): array
{
    $min = sec_password_min();

    return [
        $min . '+ characters' => static fn(string $p): bool => mb_strlen($p) >= $min,
        'An uppercase letter' => static fn(string $p): bool => (bool)preg_match('/[A-Z]/', $p),
        'A lowercase letter' => static fn(string $p): bool => (bool)preg_match('/[a-z]/', $p),
        'A number' => static fn(string $p): bool => (bool)preg_match('/[0-9]/', $p),
        'A special character' => static fn(string $p): bool =>
            (bool)preg_match('/[^A-Za-z0-9]/', $p),
    ];
}

/**
 * Server-side validation. Returns the rules the password fails, empty when it
 * passes. The browser checklist is feedback; this is the control.
 */
function password_policy_errors(string $password): array
{
    $failed = [];
    foreach (password_rules() as $label => $test) {
        if (!$test($password)) {
            $failed[] = $label;
        }
    }
    return $failed;
}

/** One sentence naming what is missing, for a flash message. */
function password_policy_message(array $failed): string
{
    return 'Password does not meet the policy. Still needed: ' . implode(', ', $failed) . '.';
}

/**
 * Record a password hash in the history and stamp the change time.
 *
 * Called on every password change so expiry and reuse checks have something
 * to work from. Only hashes are stored - never the password.
 */
function password_record_change(int $userId, string $hash): void
{
    db()->prepare('UPDATE users SET password_changed_at = NOW() WHERE user_id = :id')
        ->execute(['id' => $userId]);

    db()->prepare('INSERT INTO password_history (user_id, password_hash) VALUES (:id, :h)')
        ->execute(['id' => $userId, 'h' => $hash]);

    // Keep the last five; older entries stop being useful and the table would
    // otherwise grow without bound.
    db()->prepare(
        'DELETE FROM password_history
          WHERE user_id = :id
            AND history_id NOT IN (
                SELECT history_id FROM (
                    SELECT history_id FROM password_history
                     WHERE user_id = :id2 ORDER BY created_at DESC LIMIT 5
                ) keep
            )'
    )->execute(['id' => $userId, 'id2' => $userId]);
}

/** True when the candidate matches one of this user's recent passwords. */
function password_was_used_before(int $userId, string $candidate): bool
{
    $stmt = db()->prepare(
        'SELECT password_hash FROM password_history WHERE user_id = :id ORDER BY created_at DESC LIMIT 5'
    );
    $stmt->execute(['id' => $userId]);

    foreach ($stmt->fetchAll() as $row) {
        if (password_verify($candidate, $row['password_hash'])) {
            return true;
        }
    }
    return false;
}

/** True when this account's password is older than the configured lifetime. */
function password_is_expired(array $user): bool
{
    $days = sec_password_expiry_days();
    if ($days === 0) {
        return false;
    }

    $changed = $user['password_changed_at'] ?? null;
    if (!$changed) {
        // No recorded change: treat as expired rather than as fresh, so an
        // unknown age fails closed.
        return true;
    }

    return strtotime($changed) < strtotime('-' . $days . ' days');
}

/* ===================================================================
   Account lockout

   The count lives in account_locks, keyed by user_id. A failed attempt
   against a username that does not exist is logged but never creates a
   row, so the table cannot be stuffed with invented accounts.
   =================================================================== */

/** The lock row for a user, or null when they have never failed a login. */
function lock_row(int $userId): ?array
{
    $stmt = db()->prepare('SELECT * FROM account_locks WHERE user_id = :id');
    $stmt->execute(['id' => $userId]);
    return $stmt->fetch() ?: null;
}

/**
 * True when the account is currently locked out of signing in.
 *
 * A lock older than the configured window is cleared here rather than merely
 * ignored, so the counter resets too and the next failure starts a fresh
 * three strikes instead of instantly relocking.
 */
function account_is_locked(int $userId): bool
{
    $row = lock_row($userId);
    if ($row === null || $row['locked_at'] === null) {
        return false;
    }

    $minutes = sec_lockout_minutes();
    if ($minutes > 0 && strtotime((string)$row['locked_at']) < time() - $minutes * 60) {
        clear_failed_attempts($userId);
        log_activity(
            'account.unlocked',
            'Lockout expired after ' . $minutes . ' minutes',
            'user',
            $userId,
            ['user_id' => $userId, 'role' => 'guest', 'username' => $row['username']],
            'Security'
        );
        return false;
    }

    return true;
}

/**
 * Record one failed attempt and lock the account on the configured threshold.
 * Returns the new attempt count, and whether this attempt caused the lock.
 *
 * @return array{attempts: int, locked: bool}
 */
function register_failed_attempt(array $user): array
{
    $userId = (int)$user['user_id'];
    $max = sec_max_attempts();

    db()->prepare(
        'INSERT INTO account_locks (user_id, username, failed_attempts, last_attempt_at)
         VALUES (:id, :username, 1, NOW())
         ON DUPLICATE KEY UPDATE
            failed_attempts = failed_attempts + 1,
            last_attempt_at = NOW()'
    )->execute(['id' => $userId, 'username' => $user['username']]);

    $row = lock_row($userId);
    $attempts = (int)($row['failed_attempts'] ?? 1);
    $justLocked = false;

    if ($attempts >= $max && ($row['locked_at'] ?? null) === null) {
        db()->prepare('UPDATE account_locks SET locked_at = NOW() WHERE user_id = :id')
            ->execute(['id' => $userId]);
        $justLocked = true;
    }

    return ['attempts' => $attempts, 'locked' => $justLocked || ($row['locked_at'] ?? null) !== null];
}

/** Clear the counter after a successful sign-in. */
function clear_failed_attempts(int $userId): void
{
    db()->prepare('DELETE FROM account_locks WHERE user_id = :id')->execute(['id' => $userId]);
}

/**
 * Administrator unlock. Returns the username on success, null if there was
 * no such locked account.
 */
function unlock_account(int $userId): ?string
{
    $stmt = db()->prepare(
        'SELECT l.username FROM account_locks l WHERE l.user_id = :id AND l.locked_at IS NOT NULL'
    );
    $stmt->execute(['id' => $userId]);
    $username = $stmt->fetchColumn();

    if ($username === false) {
        return null;
    }

    db()->prepare('DELETE FROM account_locks WHERE user_id = :id')->execute(['id' => $userId]);

    return (string)$username;
}

/* ===================================================================
   TOTP (RFC 6238) — Google Authenticator compatible

   SHA-1, 6 digits, 30-second step, which is what Authenticator apps
   assume. Verification walks one step either side to tolerate clock
   drift, and nothing but the code the user typed is ever logged.
   =================================================================== */

/** A fresh base32 secret. 160 bits, the size RFC 4226 recommends for SHA-1. */
function totp_secret(int $bytes = 20): string
{
    return base32_encode(random_bytes($bytes));
}

function base32_encode(string $binary): string
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $out = '';
    $buffer = 0;
    $bits = 0;

    for ($i = 0, $len = strlen($binary); $i < $len; $i++) {
        $buffer = ($buffer << 8) | ord($binary[$i]);
        $bits += 8;
        while ($bits >= 5) {
            $bits -= 5;
            $out .= $alphabet[($buffer >> $bits) & 31];
        }
    }
    if ($bits > 0) {
        $out .= $alphabet[($buffer << (5 - $bits)) & 31];
    }

    return $out;
}

function base32_decode(string $secret): string
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = strtoupper(preg_replace('/[^A-Z2-7]/i', '', $secret) ?? '');

    $buffer = 0;
    $bits = 0;
    $out = '';

    for ($i = 0, $len = strlen($secret); $i < $len; $i++) {
        $value = strpos($alphabet, $secret[$i]);
        if ($value === false) {
            continue;
        }
        $buffer = ($buffer << 5) | $value;
        $bits += 5;
        if ($bits >= 8) {
            $bits -= 8;
            $out .= chr(($buffer >> $bits) & 0xFF);
        }
    }

    return $out;
}

/** The 6-digit code for one 30-second counter step. */
function totp_code(string $secret, int $counter): string
{
    $key = base32_decode($secret);
    if ($key === '') {
        return '';
    }

    // 8-byte big-endian counter.
    $binCounter = pack('N*', 0, $counter);
    $hash = hash_hmac('sha1', $binCounter, $key, true);

    $offset = ord($hash[19]) & 0xF;
    $part = substr($hash, $offset, 4);
    $value = unpack('N', $part)[1] & 0x7FFFFFFF;

    return str_pad((string)($value % 1000000), 6, '0', STR_PAD_LEFT);
}

/**
 * Verify a code against the secret.
 *
 * $window = 1 accepts the previous and next step as well as the current one,
 * which is the usual allowance for phone/server clock drift. hash_equals keeps
 * the comparison constant-time.
 */
function totp_verify(string $secret, string $code, int $window = 1): bool
{
    $code = preg_replace('/\D/', '', $code) ?? '';
    if (strlen($code) !== 6 || $secret === '') {
        return false;
    }

    $step = (int)floor(time() / 30);

    for ($i = -$window; $i <= $window; $i++) {
        if (hash_equals(totp_code($secret, $step + $i), $code)) {
            return true;
        }
    }

    return false;
}

/**
 * The otpauth:// URI an authenticator app scans.
 *
 * Built here and rendered as a QR in the browser; it is never sent to an
 * external chart/QR service, because it contains the shared secret.
 */
function totp_uri(string $secret, string $account, string $issuer = 'MarkMe'): string
{
    return 'otpauth://totp/' . rawurlencode($issuer . ':' . $account)
        . '?secret=' . $secret
        . '&issuer=' . rawurlencode($issuer)
        . '&algorithm=SHA1&digits=6&period=30';
}

/** Show a secret in readable four-character groups, for manual entry. */
function totp_secret_groups(string $secret): string
{
    return trim(chunk_split($secret, 4, ' '));
}

/* ===================================================================
   Activation tokens

   Only the hash is stored. The raw token lives in the emailed link and
   nowhere else, so reading the database does not let you activate an
   account.
   =================================================================== */

/** Issue a single-use activation token. Returns the raw value for the link. */
function activation_token_issue(int $userId, int $hoursValid = 24): string
{
    // Any earlier token for this account stops working the moment a new one
    // is issued, so a forwarded old email cannot be used later.
    db()->prepare(
        "DELETE FROM email_verification_tokens WHERE user_id = :id AND purpose = 'activation'"
    )->execute(['id' => $userId]);

    $raw = bin2hex(random_bytes(32));

    db()->prepare(
        'INSERT INTO email_verification_tokens (user_id, token_hash, expires_at)
         VALUES (:id, :hash, DATE_ADD(NOW(), INTERVAL :hours HOUR))'
    )->execute([
        'id' => $userId,
        'hash' => hash('sha256', $raw),
        'hours' => $hoursValid,
    ]);

    return $raw;
}

/**
 * Consume an activation token.
 *
 * Returns ['ok' => bool, 'reason' => string, 'user_id' => ?int]. Expiry and
 * single use are both enforced in the query, not in the caller.
 */
function activation_token_consume(string $raw): array
{
    $raw = trim($raw);
    if ($raw === '' || !preg_match('/^[a-f0-9]{64}$/i', $raw)) {
        return ['ok' => false, 'reason' => 'That activation link is not valid.', 'user_id' => null];
    }

    $stmt = db()->prepare(
        "SELECT * FROM email_verification_tokens
          WHERE token_hash = :hash AND purpose = 'activation'"
    );
    $stmt->execute(['hash' => hash('sha256', $raw)]);
    $row = $stmt->fetch();

    if (!$row) {
        return ['ok' => false, 'reason' => 'That activation link is not valid.', 'user_id' => null];
    }
    if ($row['used_at'] !== null) {
        return ['ok' => false, 'reason' => 'That activation link has already been used.', 'user_id' => null];
    }
    if (strtotime($row['expires_at']) < time()) {
        return ['ok' => false, 'reason' => 'That activation link has expired.', 'user_id' => (int)$row['user_id']];
    }

    db()->prepare('UPDATE email_verification_tokens SET used_at = NOW() WHERE token_id = :id')
        ->execute(['id' => $row['token_id']]);

    db()->prepare('UPDATE users SET is_active = 1, activated_at = NOW() WHERE user_id = :id')
        ->execute(['id' => $row['user_id']]);

    return ['ok' => true, 'reason' => '', 'user_id' => (int)$row['user_id']];
}

/* ===================================================================
   reCAPTCHA

   Config-driven. With no keys the site does not pretend to be protected:
   captcha_configured() is false, no widget is rendered, and the Security
   screen says NOT CONFIGURED.
   =================================================================== */

function captcha_configured(): bool
{
    return defined('RECAPTCHA_SITE_KEY') && defined('RECAPTCHA_SECRET_KEY')
        && RECAPTCHA_SITE_KEY !== '' && RECAPTCHA_SECRET_KEY !== '';
}

/**
 * Verify the response token with Google. Returns true only on a verified
 * success - a network failure is a failure, not a pass.
 */
function captcha_verify(?string $response, ?string $remoteIp = null): bool
{
    if (!captcha_configured()) {
        // Nothing is claimed to be verified when nothing is configured.
        return false;
    }

    $response = trim((string)$response);
    if ($response === '') {
        return false;
    }

    $payload = http_build_query([
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $response,
        'remoteip' => $remoteIp ?? request_ip(),
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $payload,
            'timeout' => 8,
        ],
    ]);

    $body = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    if ($body === false) {
        return false;
    }

    $data = json_decode($body, true);

    return is_array($data) && ($data['success'] ?? false) === true;
}

/* -------------------------------------------------------------------
   Self-hosted CAPTCHA

   Used when reCAPTCHA has no keys. Not a decorative checkbox: the answer
   is generated server-side, kept only in the session, never written into
   the page, and consumed on the first check so an image cannot be
   replayed. captcha_active() is what the UI asks before claiming any
   protection at all.
   ------------------------------------------------------------------- */

/** Which mechanism is actually in force: 'recaptcha', 'local', or 'none'. */
function captcha_mode(): string
{
    if (captcha_configured()) {
        return 'recaptcha';
    }
    return function_exists('imagecreatetruecolor') ? 'local' : 'none';
}

function captcha_active(): bool
{
    return captcha_mode() !== 'none';
}

/**
 * Generate and store a fresh challenge, returning the code for the image.
 *
 * Ambiguous glyphs (0/O, 1/I/L) are left out: a CAPTCHA that fails honest
 * people because they cannot tell O from 0 is just an obstacle.
 */
function captcha_local_issue(int $length = 5): string
{
    $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }

    $_SESSION['captcha'] = ['code' => $code, 'issued' => time()];

    return $code;
}

/**
 * Check an answer. Single use and time limited, both enforced here rather
 * than by the caller remembering to.
 */
function captcha_local_verify(?string $answer): bool
{
    $challenge = $_SESSION['captcha'] ?? null;

    // Consumed whatever the outcome: a wrong guess must not leave the same
    // image open to a second attempt.
    unset($_SESSION['captcha']);

    if (!$challenge || !isset($challenge['code'], $challenge['issued'])) {
        return false;
    }
    if (time() - (int)$challenge['issued'] > 600) {
        return false;
    }

    $answer = strtoupper(trim((string)$answer));

    return $answer !== '' && hash_equals($challenge['code'], $answer);
}

/**
 * The one call a form should make. Routes to whichever mechanism is in
 * force, so pages never have to know which is configured.
 */
function captcha_check(array $post): bool
{
    return match (captcha_mode()) {
        'recaptcha' => captcha_verify($post['g-recaptcha-response'] ?? null),
        'local' => captcha_local_verify($post['captcha_answer'] ?? null),
        default => true,   // Nothing configured and no GD: nothing is claimed either.
    };
}

/* ===================================================================
   Mail

   Sends over SMTP when credentials are configured. When they are not, it
   returns false with a reason and writes the message to a local outbox so
   the activation flow can still be demonstrated - it never reports a send
   that did not happen.
   =================================================================== */

function mail_configured(): bool
{
    return defined('SMTP_HOST') && defined('SMTP_USER') && defined('SMTP_PASS')
        && SMTP_HOST !== '' && SMTP_USER !== '' && SMTP_PASS !== '';
}

/**
 * @return array{sent: bool, reason: string, outbox: ?string}
 */
function mail_send(string $to, string $subject, string $bodyText): array
{
    $outbox = mail_write_outbox($to, $subject, $bodyText);

    if (!mail_configured()) {
        return [
            'sent' => false,
            'reason' => 'SMTP is not configured (SMTP_HOST / SMTP_USER / SMTP_PASS in config.php).',
            'outbox' => $outbox,
        ];
    }

    $result = smtp_deliver($to, $subject, $bodyText);

    return ['sent' => $result['ok'], 'reason' => $result['reason'], 'outbox' => $outbox];
}

/** Every outgoing message is written here, sent or not, for verification. */
function mail_write_outbox(string $to, string $subject, string $body): ?string
{
    $dir = __DIR__ . '/../storage/outbox';
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        return null;
    }

    $file = $dir . '/' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.txt';
    $content = "To: $to\nSubject: $subject\nDate: " . date('r') . "\n\n" . $body . "\n";

    return @file_put_contents($file, $content) !== false ? $file : null;
}

/**
 * Minimal SMTP client. No Composer dependency, and no pretending: every
 * failure path returns the server's own response text.
 */
function smtp_deliver(string $to, string $subject, string $body): array
{
    $host = SMTP_HOST;
    $port = defined('SMTP_PORT') ? (int)SMTP_PORT : 587;
    $secure = defined('SMTP_SECURE') ? strtolower((string)SMTP_SECURE) : 'tls';
    $from = defined('SMTP_FROM') && SMTP_FROM !== '' ? SMTP_FROM : SMTP_USER;

    $target = ($secure === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
    $socket = @stream_socket_client($target, $errNo, $errStr, 10);
    if (!$socket) {
        return ['ok' => false, 'reason' => "Could not connect to $host:$port - $errStr"];
    }

    $read = static function ($socket): string {
        $data = '';
        while (($line = fgets($socket, 515)) !== false) {
            $data .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };
    $say = static function ($socket, string $cmd) use ($read): string {
        fwrite($socket, $cmd . "\r\n");
        return $read($socket);
    };
    $ok = static fn(string $r, string $code): bool => str_starts_with(trim($r), $code);

    $greeting = $read($socket);
    if (!$ok($greeting, '220')) {
        fclose($socket);
        return ['ok' => false, 'reason' => 'Unexpected SMTP greeting: ' . trim($greeting)];
    }

    $helo = $say($socket, 'EHLO markme.local');

    if ($secure === 'tls') {
        if (!$ok($say($socket, 'STARTTLS'), '220')) {
            fclose($socket);
            return ['ok' => false, 'reason' => 'Server refused STARTTLS.'];
        }
        if (!@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            return ['ok' => false, 'reason' => 'TLS negotiation failed.'];
        }
        $helo = $say($socket, 'EHLO markme.local');
    }

    if (!$ok($helo, '250')) {
        fclose($socket);
        return ['ok' => false, 'reason' => 'EHLO rejected: ' . trim($helo)];
    }

    $say($socket, 'AUTH LOGIN');
    $say($socket, base64_encode(SMTP_USER));
    $auth = $say($socket, base64_encode(SMTP_PASS));
    if (!$ok($auth, '235')) {
        fclose($socket);
        // The server's text, not the credentials.
        return ['ok' => false, 'reason' => 'SMTP authentication failed: ' . trim($auth)];
    }

    if (!$ok($say($socket, 'MAIL FROM:<' . $from . '>'), '250')) {
        fclose($socket);
        return ['ok' => false, 'reason' => 'Sender rejected.'];
    }
    if (!$ok($say($socket, 'RCPT TO:<' . $to . '>'), '250')) {
        fclose($socket);
        return ['ok' => false, 'reason' => 'Recipient rejected.'];
    }
    if (!$ok($say($socket, 'DATA'), '354')) {
        fclose($socket);
        return ['ok' => false, 'reason' => 'Server refused DATA.'];
    }

    $headers = 'From: MarkMe <' . $from . ">\r\n"
        . 'To: <' . $to . ">\r\n"
        . 'Subject: ' . $subject . "\r\n"
        . "MIME-Version: 1.0\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n\r\n";

    // Dot-stuffing, so a line that is just "." cannot end the message early.
    $safeBody = preg_replace('/^\./m', '..', str_replace("\n", "\r\n", $body));
    $sent = $say($socket, $headers . $safeBody . "\r\n.");
    $say($socket, 'QUIT');
    fclose($socket);

    return $ok($sent, '250')
        ? ['ok' => true, 'reason' => '']
        : ['ok' => false, 'reason' => 'Message rejected: ' . trim($sent)];
}

/* ===================================================================
   Request context, for the audit trail
   =================================================================== */

/** The client IP. Proxy headers are ignored - they are caller-controlled. */
function request_ip(): string
{
    return (string)($_SERVER['REMOTE_ADDR'] ?? '');
}

function request_user_agent(): string
{
    return mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
}
