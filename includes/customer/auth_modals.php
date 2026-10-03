<?php
// Rendered from footer.php on pages that only loaded functions.php, so the
// security helpers used below are required here rather than assumed.
require_once __DIR__ . '/../security.php';

$reopenModal = flash_get('reopen_modal');

/* Arriving straight from a sign-out. The session is gone by then, so this
   cannot be a flash - it rides on the query string instead. */
if ($reopenModal === null && isset($_GET['signedout'])) {
    $reopenModal = 'login';
    $justSignedOut = true;
}
$loginError = flash_get('login_error');
$loginSuccess = flash_get('login_success');
$loginOldIdentifier = flash_get('login_old_identifier');
$loginReturnTo = flash_get('login_return_to');
$registerErrors = flash_get('register_errors');
$registerOld = flash_get('register_old');
$registerReturnTo = flash_get('register_return_to');
// Only set when SMTP is unconfigured: the link that would have been emailed.
$activationLink = flash_get('activation_link');
$here = current_path();
?>
<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered auth-modal-dialog">
        <div class="modal-content auth-modal-content">
            <button type="button" class="btn-close auth-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="auth-visual">
                <div class="brand-mark">MarkMe</div>
                <blockquote>"Every page you've loved deserves a bookmark that's just as personal."</blockquote>
                <div class="auth-shapes" aria-hidden="true">
                    <span style="background:var(--gold);height:108px"></span>
                    <span style="background:var(--accent);height:128px"></span>
                    <span style="background:var(--cream);height:96px"></span>
                </div>
            </div>
            <div class="auth-form-panel">
                <h1 id="loginModalLabel">Welcome back</h1>
                <p class="subtitle">Log in to keep designing and tracking your bookmarks.</p>

                <?php if ($loginSuccess): ?><div class="alert alert-success d-flex gap-2 align-items-start" role="status"><?= icon("check-circle", 18) ?><span><?= e($loginSuccess) ?></span></div><?php endif; ?>
                <?php if ($activationLink): ?>
                    <!-- Shown only because SMTP is not configured on this server.
                         Configure SMTP_* in config.php and this disappears: the
                         link then goes to the address the account registered with,
                         which is the point of activating by email. -->
                    <div class="alert alert-warning" role="alert" style="font-size:.85rem">
                        <strong>Email is not configured on this server.</strong>
                        The activation link below is shown here instead of being sent.
                        <a href="<?= e($activationLink) ?>" style="word-break:break-all;display:block;margin-top:.4rem">
                            <?= e($activationLink) ?>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if ($loginError): ?><div class="alert alert-danger d-flex gap-2 align-items-start" role="alert"><?= icon("alert", 18) ?><span><?= e($loginError) ?></span></div><?php endif; ?>

                <form method="post" action="<?= BASE_URL ?>/customer/login.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="return_to" value="<?= e($loginReturnTo ?? $here) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="loginIdentifier">Username or email</label>
                        <input type="text" id="loginIdentifier" name="identifier" autocomplete="username" class="form-control" value="<?= e($loginOldIdentifier ?? '') ?>" required <?= $reopenModal === 'login' ? 'autofocus' : '' ?>>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="loginPassword">Password</label>
                        <input type="password" id="loginPassword" name="password" class="form-control" autocomplete="current-password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Log in</button>
                </form>
                <?php if (setting('allow_registration', '1') === '1'): ?>
                    <p class="auth-switch">No account yet?
                        <button type="button" class="btn btn-link p-0 align-baseline" data-bs-toggle="modal" data-bs-target="#registerModal">Register</button>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Register Modal — only rendered while signups are open. -->
<?php if (setting('allow_registration', '1') === '1'): ?>
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered auth-modal-dialog auth-modal-dialog-wide">
        <div class="modal-content auth-modal-content">
            <button type="button" class="btn-close auth-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="auth-visual">
                <div class="brand-mark">MarkMe</div>
                <blockquote>"Join MarkMe and start designing a bookmark that tells your story."</blockquote>
                <div class="auth-shapes" aria-hidden="true">
                    <span style="background:var(--gold);height:108px"></span>
                    <span style="background:var(--accent);height:128px"></span>
                    <span style="background:var(--cream);height:96px"></span>
                </div>
            </div>
            <div class="auth-form-panel">
                <h1 id="registerModalLabel">Create an account</h1>
                <p class="subtitle">It only takes a minute to start personalizing.</p>

                <?php if ($registerErrors): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0"><?php foreach ($registerErrors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= BASE_URL ?>/customer/register.php" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="return_to" value="<?= e($registerReturnTo ?? $here) ?>">
                    <div class="form-grid">
                        <div>
                            <label class="form-label" for="regFullName">Full name</label>
                            <input type="text" id="regFullName" name="full_name" autocomplete="name" class="form-control" value="<?= e($registerOld['full_name'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label class="form-label" for="regUsername">Username</label>
                            <input type="text" id="regUsername" name="username" autocomplete="username" class="form-control" value="<?= e($registerOld['username'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label class="form-label" for="regEmail">Email</label>
                            <input type="email" id="regEmail" name="email" autocomplete="email" class="form-control" value="<?= e($registerOld['email'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label class="form-label" for="regContact">Contact number</label>
                            <input type="text" id="regContact" name="contact_number" autocomplete="tel" class="form-control" value="<?= e($registerOld['contact_number'] ?? '') ?>">
                        </div>
                        <div class="span-2">
                            <label class="form-label" for="regAddress">Delivery address</label>
                            <textarea id="regAddress" name="address" autocomplete="street-address" class="form-control" rows="2"><?= e($registerOld['address'] ?? '') ?></textarea>
                        </div>
                        <div>
                            <label class="form-label" for="regPassword">Password</label>
                            <input type="password" id="regPassword" name="password" autocomplete="new-password"
                                   class="form-control" minlength="<?= sec_password_min() ?>" required
                                   data-password-input aria-describedby="regPolicy">
                        </div>
                        <div>
                            <label class="form-label" for="regConfirm">Confirm password</label>
                            <input type="password" id="regConfirm" name="confirm_password" autocomplete="new-password"
                                   class="form-control" minlength="<?= sec_password_min() ?>" required>
                        </div>
                    </div>

                    <!-- The checklist is built from password_rules(), the same
                         list the server enforces, so it cannot drift from it.
                         This is feedback; register.php is the control. -->
                    <ul class="pw-policy" id="regPolicy" data-password-policy aria-live="polite">
                        <?php foreach (array_keys(password_rules()) as $rule): ?>
                            <li data-rule="<?= e($rule) ?>">
                                <span class="pw-mark" aria-hidden="true"></span><?= e($rule) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if (captcha_mode() === 'recaptcha'): ?>
                        <div class="g-recaptcha" style="margin-top:var(--s-5)"
                             data-sitekey="<?= e(RECAPTCHA_SITE_KEY) ?>"></div>
                        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                    <?php elseif (captcha_mode() === 'local'): ?>
                        <!-- The answer is only ever in the session; this markup
                             carries the picture, never the code. -->
                        <div class="captcha-row">
                            <img src="<?= BASE_URL ?>/auth/captcha_image.php" alt=""
                                 class="captcha-img" width="190" height="60" data-captcha-img>
                            <button type="button" class="btn-icon captcha-reload" data-captcha-reload
                                    aria-label="Get a different image">
                                <?= icon('refresh', 17) ?>
                            </button>
                            <div class="captcha-field">
                                <label class="form-label" for="regCaptcha">Type the characters</label>
                                <input type="text" id="regCaptcha" name="captcha_answer"
                                       class="form-control" autocomplete="off" spellcheck="false"
                                       maxlength="8" required
                                       style="text-transform:uppercase;letter-spacing:.18em">
                            </div>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:var(--s-6)">Create account</button>
                </form>
                <p class="auth-switch">Already have an account?
                    <button type="button" class="btn btn-link p-0 align-baseline" data-bs-toggle="modal" data-bs-target="#loginModal">Log in</button>
                </p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Login-required prompt.
     Shown when a guest tries to customize, favourite or add to cart. It is a
     courtesy, not the security boundary: add_to_cart.php, design_save.php and
     the studio itself all call require_login() server-side regardless. -->
<div class="modal fade" id="loginRequiredModal" tabindex="-1"
     aria-labelledby="loginRequiredLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm-custom">
        <div class="modal-content gate-modal">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <span class="gate-icon" aria-hidden="true"><?= icon('user', 26) ?></span>
            <h2 id="loginRequiredLabel">Login required</h2>
            <p data-gate-message>Please log in or create an account to customize your bookmark.</p>
            <div class="gate-actions">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#loginModal">Login</button>
                <?php if (setting('allow_registration', '1') === '1'): ?>
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#registerModal">Register</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($reopenModal === 'login' || $reopenModal === 'register'): ?>
    <!-- Which modal to open, and why. The behaviour lives in script.js; this
         only states the server's intent. "greeting" marks the once-a-session
         welcome, which yields to an explicit #section link; a failed sign-in
         or registration is not a greeting and always reopens. -->
    <div data-open-modal="<?= $reopenModal === 'register' ? 'registerModal' : 'loginModal' ?>"
         data-modal-reason="<?= $loginError || $registerErrors || !empty($justSignedOut)
             ? 'response' : 'greeting' ?>"
         hidden></div>
<?php endif; ?>
