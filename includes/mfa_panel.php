<?php
/**
 * The two-factor authentication panel, shared by customer Settings and admin
 * Profile so both sides enrol the same way.
 *
 * The QR code is drawn in the browser from the otpauth:// URI. That URI
 * carries the shared secret, so it is deliberately never handed to an
 * external chart or QR image service — the only places it exists are this
 * page, the session, and the user's phone.
 */
require_once __DIR__ . '/security.php';

$mfaStmt = db()->prepare('SELECT mfa_enabled, mfa_confirmed_at, email FROM users WHERE user_id = :id');
$mfaStmt->execute(['id' => (int)current_user()['user_id']]);
$mfaRow = $mfaStmt->fetch() ?: ['mfa_enabled' => 0, 'mfa_confirmed_at' => null, 'email' => ''];

$mfaOn = (int)$mfaRow['mfa_enabled'] === 1;
$mfaCandidate = $_SESSION['mfa_candidate'] ?? '';
$mfaUri = $mfaCandidate !== ''
    ? totp_uri($mfaCandidate, (string)$mfaRow['email'])
    : '';
?>
<section class="card card-flush" id="mfa">
    <div class="panel-head">
        <h2 class="d-flex align-items-center gap-2"><?= icon('shield', 18) ?> Two-factor authentication</h2>
        <span class="badge bg-<?= $mfaOn ? 'success' : 'secondary' ?>">
            <?= $mfaOn ? 'On' : 'Off' ?>
        </span>
    </div>
    <div class="panel-body">
        <?php if ($mfaOn): ?>
            <div class="setting-row">
                <div>
                    <p class="setting-title">Your account asks for a code at sign-in</p>
                    <p class="form-text" style="margin:0">
                        Enabled <?= e(date('M j, Y', strtotime((string)$mfaRow['mfa_confirmed_at']))) ?>.
                        You will need your authenticator app every time you sign in.
                    </p>
                </div>
            </div>

            <form method="post" action="<?= BASE_URL ?>/auth/mfa_setup.php" style="margin-top:var(--s-5)"
                  data-confirm
                  data-confirm-title="Turn off two-factor authentication?"
                  data-confirm-body="Your password alone will be enough to sign in."
                  data-confirm-note="You can turn it back on at any time."
                  data-confirm-action="Turn it off">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="disable">
                <div class="field-group" style="max-width:320px">
                    <label class="form-label" for="mfaOffPw">Confirm your password to turn it off</label>
                    <input type="password" id="mfaOffPw" name="password" class="form-control"
                           autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-outline-danger">
                    <?= icon('close', 16) ?> Turn off two-factor authentication
                </button>
            </form>

        <?php elseif ($mfaCandidate !== ''): ?>
            <p style="margin-top:0">Scan this with Google Authenticator, then enter the
                6-digit code it shows to finish. Nothing changes on your account until
                that code is verified.</p>

            <div class="mfa-enrol">
                <div class="mfa-qr">
                    <div id="mfaQr" data-otpauth="<?= e($mfaUri) ?>"
                         aria-label="QR code for your authenticator app"></div>
                    <noscript>
                        <p class="form-text">Enter the key below into your app by hand.</p>
                    </noscript>
                </div>

                <div class="mfa-enrol-main">
                    <p class="eyebrow eyebrow-muted">Or enter this key by hand</p>
                    <p class="mono-num mfa-secret"><?= e(totp_secret_groups($mfaCandidate)) ?></p>

                    <form method="post" action="<?= BASE_URL ?>/auth/mfa_setup.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="confirm">
                        <div class="field-group" style="max-width:220px">
                            <label class="form-label" for="mfaCode">Code from the app</label>
                            <input type="text" id="mfaCode" name="code" class="form-control mono-num"
                                   inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required
                                   autocomplete="one-time-code"
                                   style="letter-spacing:.3em;text-align:center">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <?= icon('check', 16) ?> Verify and turn on
                        </button>
                    </form>
                </div>
            </div>

        <?php else: ?>
            <div class="setting-row">
                <div>
                    <p class="setting-title">Add a second step to your sign-in</p>
                    <p class="form-text" style="margin:0">
                        With this on, your password alone is not enough to get into your
                        account — you will also need a code from Google Authenticator or
                        any other TOTP app.
                    </p>
                </div>
                <form method="post" action="<?= BASE_URL ?>/auth/mfa_setup.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="start">
                    <button type="submit" class="btn btn-secondary btn-sm">
                        <?= icon('shield', 15) ?> Set up
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($mfaCandidate !== ''): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<?php endif; ?>
