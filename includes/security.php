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
    // 900s when unset: the same working default the Security screen offers.
    return max(30, (int)setting('session_timeout_secs', '900'));
}

/** Minimum password length. Required baseline: 12. */
function sec_password_min(): int
{
    return max(8, (int)setting('password_min_length', '12'));
}

/**
 * How many characters of a given class a password must contain.
 *
 * A count rather than a yes/no, so a policy can ask for two digits or three
 * symbols rather than only "at least one". Zero means the class is not
 * required at all, which is what the old on/off setting expressed.
 *
 * Reads password_min_<class>, falling back to the earlier
 * password_require_<class> flag so an installation that was configured before
 * counts existed keeps the policy it already had.
 *
 * Only the classes are adjustable. The length floor in sec_password_min() is
 * not: eight characters is the lowest this will go whatever is saved.
 */
function sec_password_min_class(string $class): int
{
    if (!in_array($class, password_classes(), true)) {
        return 0;
    }

    /* '' is the "absent" marker: setting() wants a string default, and a key
       that really holds '0' must not be mistaken for one that is unset. */
    $stored = setting('password_min_' . $class, '');

    if ($stored === '') {
        // Pre-counts configuration: the flag meant "at least one".
        return setting('password_require_' . $class, '1') === '1' ? 1 : 0;
    }

    return max(0, min(64, (int)$stored));
}

/** The character classes a policy can ask for, in the order they are shown. */
function password_classes(): array
{
    return ['upper', 'lower', 'digit', 'symbol'];
}

/**
 * The regex matching one character of a class, and how to name it.
 *
 * One definition so the rule, its label and the admin screen cannot describe
 * different things.
 */
function password_class_spec(string $class): array
{
    return [
        'upper' => ['pattern' => '/[A-Z]/', 'one' => 'An uppercase letter', 'many' => 'uppercase letters', 'hint' => 'A to Z'],
        'lower' => ['pattern' => '/[a-z]/', 'one' => 'A lowercase letter', 'many' => 'lowercase letters', 'hint' => 'a to z'],
        'digit' => ['pattern' => '/[0-9]/', 'one' => 'A number', 'many' => 'numbers', 'hint' => '0 to 9'],
        'symbol' => ['pattern' => '/[^A-Za-z0-9]/', 'one' => 'A special character', 'many' => 'special characters',
                     'hint' => 'anything that is not a letter or a number'],
    ][$class] ?? ['pattern' => '/$^/', 'one' => '', 'many' => '', 'hint' => ''];
}

/** How a requirement of $count of $class should read. */
function password_class_label(string $class, int $count): string
{
    $spec = password_class_spec($class);

    return $count === 1 ? $spec['one'] : $count . ' ' . $spec['many'];
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

    /* Length is always a rule. Each character class is one too, but only when
       the policy asks for at least one of it - a class set to zero is absent
       from this list entirely, so it disappears from the checklist the user
       sees and from the check the server runs at the same moment. The two
       cannot disagree about what the policy is. */
    $rules = [
        $min . '+ characters' => static fn(string $p): bool => mb_strlen($p) >= $min,
    ];

    foreach (password_classes() as $class) {
        $need = sec_password_min_class($class);
        if ($need < 1) {
            continue;
        }

        $pattern = password_class_spec($class)['pattern'];

        /* Counted, not merely detected: a policy asking for two digits has to
           reject a password carrying one. preg_match_all returns how many
           times the class appears. */
        $rules[password_class_label($class, $need)] =
            static fn(string $p): bool => preg_match_all($pattern, $p) >= $need;
    }

    return $rules;
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
const CAPTCHA_TTL_SECONDS = 600;

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
 * The code of the challenge currently in the session, without issuing a new
 * one. Used by the audio endpoint so the sound and the picture always describe
 * the same challenge; returns null when there is nothing live to read.
 */
function captcha_current_code(): ?string
{
    $challenge = $_SESSION['captcha'] ?? null;
    if (!$challenge || !isset($challenge['code'], $challenge['issued'])) {
        return null;
    }
    if (time() - (int)$challenge['issued'] > CAPTCHA_TTL_SECONDS) {
        return null;
    }

    return (string)$challenge['code'];
}

/**
 * Rate limit for the CAPTCHA endpoints, per session and per bucket.
 *
 * Issuing an image or a clip costs real work, and verifying costs a guess.
 * Without a ceiling, the refresh button is a free way to spin the CPU and the
 * answer field is a free way to brute force a five-character code. Counted in
 * the session rather than a table: it is throttling one browser, not banning
 * an address, and it must keep working when the database is busy.
 */
function captcha_rate_ok(string $bucket, int $limit = 12, int $window = 60): bool
{
    $now = time();
    $key = 'captcha_rate_' . $bucket;
    $hits = $_SESSION[$key] ?? [];

    // Drop anything that has aged out of the window.
    $hits = array_values(array_filter($hits, static fn($t) => $now - (int)$t < $window));

    if (count($hits) >= $limit) {
        $_SESSION[$key] = $hits;
        return false;
    }

    $hits[] = $now;
    $_SESSION[$key] = $hits;

    return true;
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
    if (time() - (int)$challenge['issued'] > CAPTCHA_TTL_SECONDS) {
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
    $mailjetReady = defined('MAILJET_API_KEY')
        && defined('MAILJET_SECRET_KEY')
        && defined('MAILJET_FROM_EMAIL')
        && MAILJET_API_KEY !== ''
        && MAILJET_SECRET_KEY !== ''
        && filter_var(MAILJET_FROM_EMAIL, FILTER_VALIDATE_EMAIL);

    $smtpReady = defined('SMTP_HOST')
        && defined('SMTP_USER')
        && defined('SMTP_PASS')
        && SMTP_HOST !== ''
        && SMTP_USER !== ''
        && SMTP_PASS !== '';

    return (bool) $mailjetReady || $smtpReady;
}

/**
 * @return array{sent: bool, reason: string, outbox: ?string}
 */

/**
 * Send an email, optionally with an HTML alternative.
 *
 * The outbox continues to store the plain-text version.
 *
 * @return array{sent: bool, reason: string, outbox: ?string}
 */
function mailjet_deliver(
    string $to,
    string $subject,
    string $bodyText,
    ?string $bodyHtml = null
): array {
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'reason' => 'PHP cURL extension is unavailable.'];
    }

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)
        || preg_match('/[\r\n]/', $to)
        || preg_match('/[\r\n]/', $subject)) {
        return ['ok' => false, 'reason' => 'Invalid email address or subject.'];
    }

    $message = [
        'From' => [
            'Email' => MAILJET_FROM_EMAIL,
            'Name' => 'MarkMe',
        ],
        'To' => [
            [
                'Email' => $to,
            ],
        ],
        'Subject' => $subject,
        'TextPart' => $bodyText,
    ];

    if ($bodyHtml !== null && $bodyHtml !== '') {
        $message['HTMLPart'] = $bodyHtml;
    }

    $payload = json_encode([
        'Messages' => [$message],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($payload === false) {
        return ['ok' => false, 'reason' => 'Could not encode email payload.'];
    }

    $ch = curl_init('https://api.mailjet.com/v3.1/send');

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_USERPWD => MAILJET_API_KEY . ':' . MAILJET_SECRET_KEY,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 25,
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        return [
            'ok' => false,
            'reason' => 'Mailjet connection failed: ' . $curlError,
        ];
    }

    $data = json_decode($response, true);

    if ($status >= 200 && $status < 300
        && isset($data['Messages'][0]['Status'])
        && $data['Messages'][0]['Status'] === 'success') {
        return ['ok' => true, 'reason' => ''];
    }

    // Return a useful error without exposing credentials.
    $detail = $data['Messages'][0]['Errors'][0]['ErrorMessage']
        ?? $data['ErrorMessage']
        ?? ('Mailjet returned HTTP ' . $status . '.');

    return [
        'ok' => false,
        'reason' => (string) $detail,
    ];
}

function mail_send(
    string $to,
    string $subject,
    string $bodyText,
    ?string $bodyHtml = null
): array {
    $outbox = mail_write_outbox($to, $subject, $bodyText);

    if (!mail_configured()) {
        return [
            'sent' => false,
            'reason' => 'Mail is not configured. Set Mailjet API credentials in the environment.',
            'outbox' => $outbox,
        ];
    }

    // Prefer Mailjet over SMTP when its credentials are present.
    $mailjetReady = defined('MAILJET_API_KEY')
        && defined('MAILJET_SECRET_KEY')
        && defined('MAILJET_FROM_EMAIL')
        && MAILJET_API_KEY !== ''
        && MAILJET_SECRET_KEY !== ''
        && filter_var(MAILJET_FROM_EMAIL, FILTER_VALIDATE_EMAIL);

    $result = $mailjetReady
        ? mailjet_deliver($to, $subject, $bodyText, $bodyHtml)
        : smtp_deliver($to, $subject, $bodyText, $bodyHtml);

    return [
        'sent' => $result['ok'],
        'reason' => $result['reason'],
        'outbox' => $outbox,
    ];
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

function smtp_deliver(
    string $to,
    string $subject,
    string $body,
    ?string $html = null
): array {
    $host = SMTP_HOST;
    $port = defined('SMTP_PORT') ? (int)SMTP_PORT : 587;
    $secure = defined('SMTP_SECURE') ? strtolower((string)SMTP_SECURE) : 'tls';
    $from = defined('SMTP_FROM') && SMTP_FROM !== '' ? SMTP_FROM : SMTP_USER;

    // Prevent SMTP command/header injection.
    foreach ([$to, $from] as $address) {
        if (!filter_var($address, FILTER_VALIDATE_EMAIL)
            || preg_match('/[\r\n]/', $address)) {
            return ['ok' => false, 'reason' => 'Invalid email address.'];
        }
    }

    if (preg_match('/[\r\n]/', $subject)) {
        return ['ok' => false, 'reason' => 'Invalid email subject.'];
    }

    $target = ($secure === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
    $socket = @stream_socket_client($target, $errNo, $errStr, 10);

    if (!$socket) {
        return [
            'ok' => false,
            'reason' => "Could not connect to $host:$port - $errStr",
        ];
    }

    stream_set_timeout($socket, 15);

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

    $ok = static fn(string $response, string $code): bool =>
        str_starts_with(trim($response), $code);

    $greeting = $read($socket);

    if (!$ok($greeting, '220')) {
        fclose($socket);
        return ['ok' => false, 'reason' => 'Unexpected SMTP greeting.'];
    }

    $helo = $say($socket, 'EHLO markme.local');

    if ($secure === 'tls') {
        if (!$ok($say($socket, 'STARTTLS'), '220')) {
            fclose($socket);
            return ['ok' => false, 'reason' => 'Server refused STARTTLS.'];
        }

        if (!@stream_socket_enable_crypto(
            $socket,
            true,
            STREAM_CRYPTO_METHOD_TLS_CLIENT
        )) {
            fclose($socket);
            return ['ok' => false, 'reason' => 'TLS negotiation failed.'];
        }

        $helo = $say($socket, 'EHLO markme.local');
    }

    if (!$ok($helo, '250')) {
        fclose($socket);
        return ['ok' => false, 'reason' => 'EHLO rejected.'];
    }

    if (!$ok($say($socket, 'AUTH LOGIN'), '334')) {
        fclose($socket);
        return ['ok' => false, 'reason' => 'SMTP AUTH LOGIN was rejected.'];
    }

    if (!$ok($say($socket, base64_encode(SMTP_USER)), '334')) {
        fclose($socket);
        return ['ok' => false, 'reason' => 'SMTP username was rejected.'];
    }

    $auth = $say($socket, base64_encode(SMTP_PASS));

    if (!$ok($auth, '235')) {
        fclose($socket);
        return ['ok' => false, 'reason' => 'SMTP authentication failed.'];
    }

    if (!$ok($say($socket, 'MAIL FROM:<' . $from . '>'), '250')) {
        fclose($socket);
        return ['ok' => false, 'reason' => 'Sender rejected.'];
    }

    $recipient = $say($socket, 'RCPT TO:<' . $to . '>');

    if (!in_array(substr(trim($recipient), 0, 3), ['250', '251'], true)) {
        fclose($socket);
        return ['ok' => false, 'reason' => 'Recipient rejected.'];
    }

    if (!$ok($say($socket, 'DATA'), '354')) {
        fclose($socket);
        return ['ok' => false, 'reason' => 'Server refused DATA.'];
    }

    $subjectHeader = function_exists('mb_encode_mimeheader')
        ? mb_encode_mimeheader($subject, 'UTF-8', 'B', "\r\n")
        : '=?UTF-8?B?' . base64_encode($subject) . '?=';

    $headers = 'From: MarkMe <' . $from . ">\r\n"
        . 'To: <' . $to . ">\r\n"
        . 'Subject: ' . $subjectHeader . "\r\n"
        . "MIME-Version: 1.0\r\n";

    if ($html !== null && $html !== '') {
        $boundary = 'markme_' . bin2hex(random_bytes(16));

        $headers .= 'Content-Type: multipart/alternative; boundary="'
            . $boundary . "\"\r\n\r\n";

        $message = '--' . $boundary . "\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($body), 76, "\r\n")
            . '--' . $boundary . "\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($html), 76, "\r\n")
            . '--' . $boundary . "--\r\n";
    } else {
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n";

        $message = chunk_split(base64_encode($body), 76, "\r\n");
    }

    // SMTP requires CRLF line endings and dot-stuffing.
    $message = str_replace(["\r\n", "\r", "\n"], "\n", $message);
    $message = str_replace("\n", "\r\n", $message);
    $message = preg_replace('/^\./m', '..', $message) ?? $message;

    fwrite($socket, $headers . $message . "\r\n.\r\n");
    $sent = $read($socket);

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

/* ===================================================================
 * Email one-time codes
 *
 * A second factor for accounts that cannot or will not use an authenticator
 * app. Deliberately strict: six digits is a small space, so the code is short
 * lived, single use, guess limited, and rationed per account.
 *
 * Only the hash is stored. The code itself exists in exactly two places - the
 * email that was sent, and the request that answers the challenge - and in
 * neither case is it written to the database or the audit log.
 * =================================================================== */

/** How long a code is good for. Short, because email arrives in seconds. */
const EMAIL_OTP_TTL_SECONDS = 300;

/** Guesses allowed before the code is spent. */
const EMAIL_OTP_MAX_ATTEMPTS = 5;

/** Smallest gap between codes for one account. */
const EMAIL_OTP_COOLDOWN_SECONDS = 60;

/**
 * Is email as a second factor actually usable here?
 *
 * Without SMTP a code would be written to the local outbox and never reach
 * anybody, which is not a second factor - it is a locked door. The option is
 * offered only when mail can really be sent.
 */
function email_otp_available(): bool
{
    return mail_configured();
}

/**
 * Issue a code, returning it so the caller can send it.
 *
 * Returns null when the account asked too recently; the caller reports the
 * cooldown rather than silently doing nothing.
 */
function email_otp_issue(int $userId): ?string
{
    $recent = db()->prepare(
        'SELECT COUNT(*) FROM email_otps
          WHERE user_id = :id AND created_at > DATE_SUB(NOW(), INTERVAL :secs SECOND)'
    );
    $recent->bindValue('id', $userId, PDO::PARAM_INT);
    $recent->bindValue('secs', EMAIL_OTP_COOLDOWN_SECONDS, PDO::PARAM_INT);
    $recent->execute();
    if ((int)$recent->fetchColumn() > 0) {
        return null;
    }

    // Any earlier code stops working now, so only the newest one is live.
    db()->prepare('DELETE FROM email_otps WHERE user_id = :id')
        ->execute(['id' => $userId]);

    // random_int, not rand: this is a credential.
    $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    $stmt = db()->prepare(
        'INSERT INTO email_otps (user_id, code_hash, expires_at)
         VALUES (:id, :hash, DATE_ADD(NOW(), INTERVAL :secs SECOND))'
    );
    $stmt->bindValue('id', $userId, PDO::PARAM_INT);
    $stmt->bindValue('hash', hash('sha256', $code));
    $stmt->bindValue('secs', EMAIL_OTP_TTL_SECONDS, PDO::PARAM_INT);
    $stmt->execute();

    return $code;
}

/**
 * Check a submitted code.
 *
 * Expiry, single use and the attempt ceiling are all enforced here rather than
 * by the caller remembering to. A wrong guess costs an attempt whether or not
 * the code was ever right, so guessing is bounded.
 *
 * @return array{ok: bool, reason: string}
 */
function email_otp_verify(int $userId, string $code): array
{
    $code = preg_replace('/\D/', '', $code) ?? '';

    $stmt = db()->prepare(
        'SELECT otp_id, code_hash, attempts, expires_at, used_at
           FROM email_otps
          WHERE user_id = :id
          ORDER BY otp_id DESC
          LIMIT 1'
    );
    $stmt->execute(['id' => $userId]);
    $row = $stmt->fetch();

    if (!$row) {
        return ['ok' => false, 'reason' => 'No code is waiting. Ask for a new one.'];
    }
    if ($row['used_at'] !== null) {
        return ['ok' => false, 'reason' => 'That code has already been used. Ask for a new one.'];
    }
    if (strtotime((string)$row['expires_at']) < time()) {
        return ['ok' => false, 'reason' => 'That code has expired. Ask for a new one.'];
    }
    if ((int)$row['attempts'] >= EMAIL_OTP_MAX_ATTEMPTS) {
        return ['ok' => false, 'reason' => 'Too many attempts. Ask for a new code.'];
    }

    // The attempt is recorded before the comparison, so an abandoned request
    // still costs a guess.
    db()->prepare('UPDATE email_otps SET attempts = attempts + 1 WHERE otp_id = :id')
        ->execute(['id' => (int)$row['otp_id']]);

    if (strlen($code) !== 6 || !hash_equals((string)$row['code_hash'], hash('sha256', $code))) {
        $left = EMAIL_OTP_MAX_ATTEMPTS - ((int)$row['attempts'] + 1);
        return [
            'ok' => false,
            'reason' => 'That code is not right.'
                . ($left > 0 ? ' ' . $left . ' ' . ($left === 1 ? 'try' : 'tries') . ' left.' : ''),
        ];
    }

    // Spent on success, so the same code cannot be replayed.
    db()->prepare('UPDATE email_otps SET used_at = NOW() WHERE otp_id = :id')
        ->execute(['id' => (int)$row['otp_id']]);

    return ['ok' => true, 'reason' => ''];
}

/**
 * Issue a code and email it.
 *
 * Never reports a send that did not happen: mail_send() says whether it left
 * the building, and that answer is passed straight back.
 *
 * @return array{sent: bool, reason: string}
 */
function email_otp_send(array $user): array
{
    $code = email_otp_issue((int)$user['user_id']);
    if ($code === null) {
        return ['sent' => false, 'reason' => 'A code was sent moments ago. Please wait before asking for another.'];
    }

    $name = $user['full_name'] ?: $user['username'];
    $minutes = (int)(EMAIL_OTP_TTL_SECONDS / 60);

    $result = mail_send(
        (string)$user['email'],
        'Your MarkMe sign-in code',
        "Hello {$name},\n\n"
        . "Your sign-in code is: {$code}\n\n"
        . "It expires in {$minutes} minutes and can be used once.\n"
        . "If you did not try to sign in, you can ignore this message and your "
        . "account stays as it is.\n\n"
        . "MarkMe\n"
    );

    return [
        'sent' => $result['sent'],
        'reason' => $result['sent'] ? '' : 'The code could not be emailed.',
    ];
}
