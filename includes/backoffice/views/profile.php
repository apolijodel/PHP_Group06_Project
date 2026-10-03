<?php
/**
 * Screen: profile - markup.
 *
 * Rendered inside whichever panel shell required it - the administrator
 * panel and the staff panel share this one copy.
 *
 * Auth and form handling happen in includes/backoffice/screens/profile.php, before any
 * output. Read-only queries that were already here stayed here rather
 * than being moved for the sake of it.
 */
?>
<?php ?>

<div class="page-head">
    <div>
        <p class="eyebrow eyebrow-muted">System</p>
        <h2 style="margin-top:var(--s-2)">My profile</h2>
        <p>Your <?= e(strtolower(role_label())) ?> account.
            <?php if (can('settings.manage')): ?>
                Store-wide configuration is in
                <a href="<?= BASE_URL ?>/admin/settings.php">Settings</a>.
            <?php endif; ?></p>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger d-flex gap-2 align-items-start" role="alert"
         style="margin-bottom:var(--s-5)">
        <?= icon('alert', 20) ?>
        <ul style="margin:0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="d-flex flex-column" style="gap:var(--s-6)">
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_profile">

        <section class="card card-flush">
            <div class="panel-head">
                <h2 class="d-flex align-items-center gap-2"><?= icon('user', 18) ?> Account details</h2>
                <span class="badge badge-ok"><?= e(role_label()) ?></span>
            </div>
            <div class="panel-body">
                <div class="avatar-editor" style="margin-bottom:var(--s-6)">
                    <span class="avatar-preview" data-avatar-preview>
                        <?php if ($avatar): ?>
                            <img src="<?= e($avatar) ?>" alt="Your current profile picture">
                        <?php else: ?>
                            <span class="avatar-initials"><?= e(initials($admin['full_name'])) ?></span>
                        <?php endif; ?>
                    </span>
                    <div class="avatar-editor-controls">
                        <label class="btn btn-secondary btn-sm" for="avatar">
                            <?= icon('upload', 15) ?> Choose a picture
                        </label>
                        <input type="file" id="avatar" name="avatar" accept=".jpg,.jpeg,.png,.webp"
                               class="visually-hidden" data-avatar-input>
                        <p class="form-text" style="margin:0">JPG, PNG or WEBP, up to 2MB.</p>
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <label class="form-label" for="full_name">Full name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control"
                               value="<?= e($admin['full_name']) ?>" autocomplete="name" required>
                    </div>
                    <div>
                        <label class="form-label" for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control"
                               value="<?= e($admin['username']) ?>" autocomplete="username" required>
                    </div>
                    <div>
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control"
                               value="<?= e($admin['email']) ?>" autocomplete="email" required>
                    </div>
                    <div>
                        <label class="form-label" for="contact_number">Contact number</label>
                        <input type="tel" id="contact_number" name="contact_number" class="form-control"
                               value="<?= e($admin['contact_number']) ?>" autocomplete="tel">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top:var(--s-5)">
                    <?= icon('check', 16) ?> Save changes
                </button>
            </div>
        </section>
    </form>

    <?php require __DIR__ . '/../../mfa_panel.php'; ?>

    <section class="card card-flush">
        <div class="panel-head">
            <h2 class="d-flex align-items-center gap-2"><?= icon('key', 18) ?> Change password</h2>
        </div>
        <div class="panel-body">
            <?php if ($pwErrors): ?>
                <div class="alert alert-danger d-flex gap-2 align-items-start" role="alert">
                    <?= icon('alert', 20) ?>
                    <ul style="margin:0">
                        <?php foreach ($pwErrors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="change_password">
                <div class="form-grid">
                    <div class="span-2">
                        <label class="form-label" for="current_password">Current password</label>
                        <input type="password" id="current_password" name="current_password"
                               class="form-control" autocomplete="current-password" required>
                    </div>
                    <div>
                        <label class="form-label" for="new_password">New password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control"
                               minlength="<?= sec_password_min() ?>" autocomplete="new-password"
                               required data-password-input aria-describedby="admPolicy">
                        <ul class="pw-policy" id="admPolicy" data-password-policy aria-live="polite">
                            <?php foreach (array_keys(password_rules()) as $rule): ?>
                                <li data-rule="<?= e($rule) ?>">
                                    <span class="pw-mark" aria-hidden="true"></span><?= e($rule) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="form-text">At least 8 characters.</p>
                    </div>
                    <div>
                        <label class="form-label" for="confirm_password">Confirm new password</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               class="form-control" minlength="8" autocomplete="new-password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:var(--s-5)">
                    <?= icon('key', 16) ?> Change password
                </button>
            </form>
        </div>
    </section>
</div>
