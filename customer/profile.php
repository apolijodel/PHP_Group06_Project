<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_once __DIR__ . '/../includes/ph_locations.php';
require_login();

$userId = current_user()['user_id'];
$stmt = db()->prepare('SELECT * FROM users WHERE user_id = :id');
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    csrf_check();
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');

    if ($full_name === '') {
        $errors[] = 'Full name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        $errors[] = 'Invalid username format.';
    }
    if ($postal_code !== '' && !preg_match('/^[0-9]{4,10}$/', $postal_code)) {
        $errors[] = 'Postal code should be 4 to 10 digits.';
    }

    // Province and city are checked against the same list that builds the
    // dropdowns. The browser only filters the options; this is the real rule,
    // so a hand-crafted POST cannot store a city in the wrong province.
    if ($province !== '' && !in_array($province, ph_provinces(), true)) {
        $errors[] = 'Please choose a province from the list.';
        $province = '';
        $city = '';
    }
    if ($city !== '') {
        if ($province === '') {
            $errors[] = 'Choose a province before choosing a city.';
            $city = '';
        } elseif (!ph_is_valid_city($province, $city)) {
            $errors[] = 'That city does not belong to the selected province.';
            $city = '';
        }
    }

    if (!$errors) {
        $stmt = db()->prepare('SELECT user_id FROM users WHERE (username = :u OR email = :e) AND user_id != :id');
        $stmt->execute(['u' => $username, 'e' => $email, 'id' => $userId]);
        if ($stmt->fetch()) {
            $errors[] = 'That username or email is already taken.';
        }
    }

    // The picture is optional, but a rejected upload must not silently discard
    // the rest of the form, so it is validated alongside the text fields.
    $newAvatar = null;
    if (!$errors && !empty($_FILES['avatar']['name'])) {
        try {
            $newAvatar = handle_image_upload($_FILES['avatar'], __DIR__ . '/../uploads/avatars');
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (!$errors) {
        $sql = 'UPDATE users SET full_name = :full_name, email = :email, username = :username,
                contact_number = :contact_number, address = :address, city = :city,
                province = :province, postal_code = :postal_code';
        $params = [
            'full_name' => $full_name,
            'email' => $email,
            'username' => $username,
            'contact_number' => $contact_number ?: null,
            'address' => $address ?: null,
            'city' => $city ?: null,
            'province' => $province ?: null,
            'postal_code' => $postal_code ?: null,
            'id' => $userId,
        ];
        if ($newAvatar !== null) {
            $sql .= ', avatar_path = :avatar_path';
            $params['avatar_path'] = $newAvatar;
        }
        $sql .= ' WHERE user_id = :id';

        db()->prepare($sql)->execute($params);

        // Replace rather than accumulate: drop the previous file only once the
        // new one has been recorded.
        if ($newAvatar !== null && !empty($user['avatar_path'])) {
            $previous = __DIR__ . '/../uploads/avatars/' . basename($user['avatar_path']);
            if (is_file($previous)) {
                @unlink($previous);
            }
        }

        $_SESSION['user']['full_name'] = $full_name;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['username'] = $username;
        if ($newAvatar !== null) {
            $_SESSION['user']['avatar_path'] = $newAvatar;
        }

        flash_set('success', 'Profile updated.');
        redirect('/customer/profile.php');
    }

    // Re-render the form with what was submitted, so nothing is retyped.
    $user = array_merge($user, compact(
        'full_name', 'email', 'username', 'contact_number',
        'address', 'city', 'province', 'postal_code'
    ));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'remove_avatar') {
    csrf_check();
    if (!empty($user['avatar_path'])) {
        $previous = __DIR__ . '/../uploads/avatars/' . basename($user['avatar_path']);
        if (is_file($previous)) {
            @unlink($previous);
        }
        db()->prepare('UPDATE users SET avatar_path = NULL WHERE user_id = :id')->execute(['id' => $userId]);
        $_SESSION['user']['avatar_path'] = null;
    }
    flash_set('success', 'Profile picture removed.');
    redirect('/customer/profile.php');
}

$avatar = avatar_url($user['avatar_path']);
$currentProvince = (string)($user['province'] ?? '');
$currentCity = (string)($user['city'] ?? '');

// The whole province => cities map goes to the browser once, so changing the
// province refills the city list without a request.
$locationData = ph_locations();

// Errors mean the form re-renders with the user's edits still in it, so it
// must reopen in edit mode rather than locking their work away.
$startInEditMode = (bool)$errors;

$pageTitle = 'My Profile';
require __DIR__ . '/../includes/customer/header.php';
?>

<div class="account-page">
    <div class="d-flex flex-column" style="gap:var(--s-6)">
        <div class="page-head" style="margin-bottom:0">
            <div>
                <p class="eyebrow">Account</p>
                <h1 style="margin-top:var(--s-2)">Profile</h1>
                <p>Your details and where we deliver. Password and appearance live in
                    <a href="<?= BASE_URL ?>/customer/settings.php">Settings</a>.</p>
            </div>
        </div>

        <?php if ($errors): ?>
            <div class="alert alert-danger d-flex gap-2 align-items-start" role="alert">
                <?= icon('alert', 20) ?>
                <ul style="margin:0">
                    <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- data-profile-form drives edit mode: fields stay read-only until Edit
             Profile is pressed, Enter never submits, and only Save Changes posts. -->
        <form method="post" enctype="multipart/form-data" class="profile-form d-flex flex-column"
              style="gap:var(--s-6)" data-profile-form
              data-locked="<?= $startInEditMode ? 'false' : 'true' ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_profile">

            <!-- ------------------------ profile picture ----------------------- -->
            <section class="card card-flush">
                <div class="panel-head">
                    <h2 class="d-flex align-items-center gap-2"><?= icon('camera', 18) ?> Profile picture</h2>
                </div>
                <div class="panel-body">
                    <div class="avatar-editor">
                        <span class="avatar-preview" data-avatar-preview>
                            <?php if ($avatar): ?>
                                <img src="<?= e($avatar) ?>" alt="Your current profile picture">
                            <?php else: ?>
                                <span class="avatar-initials"><?= e(initials($user['full_name'])) ?></span>
                            <?php endif; ?>
                        </span>
                        <div class="avatar-editor-controls">
                            <label class="btn btn-secondary btn-sm" for="avatar" data-edit-only>
                                <?= icon('upload', 15) ?> Choose a picture
                            </label>
                            <input type="file" id="avatar" name="avatar" accept=".jpg,.jpeg,.png,.webp"
                                   class="visually-hidden" data-avatar-input>
                            <p class="form-text" style="margin:0">
                                JPG, PNG or WEBP, up to 2MB. It is saved when you press Save changes.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ---------------------- profile information --------------------- -->
            <section class="card card-flush">
                <div class="panel-head">
                    <h2 class="d-flex align-items-center gap-2"><?= icon('user', 18) ?> Profile information</h2>
                </div>
                <div class="panel-body">
                    <div class="form-grid">
                        <div>
                            <label class="form-label" for="full_name">Full name</label>
                            <input type="text" id="full_name" name="full_name" class="form-control"
                                   value="<?= e($user['full_name']) ?>" autocomplete="name" required>
                        </div>
                        <div>
                            <label class="form-label" for="username">Username</label>
                            <input type="text" id="username" name="username" class="form-control"
                                   value="<?= e($user['username']) ?>" autocomplete="username" required>
                            <p class="form-text">3 to 50 characters: letters, numbers and underscores.</p>
                        </div>
                        <div>
                            <label class="form-label" for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control"
                                   value="<?= e($user['email']) ?>" autocomplete="email" required>
                        </div>
                        <div>
                            <label class="form-label" for="contact_number">Contact number</label>
                            <input type="tel" id="contact_number" name="contact_number" class="form-control"
                                   value="<?= e($user['contact_number']) ?>" autocomplete="tel">
                        </div>
                    </div>
                </div>
            </section>

            <!-- --------------------- delivery information --------------------- -->
            <section class="card card-flush" id="delivery">
                <div class="panel-head">
                    <h2 class="d-flex align-items-center gap-2"><?= icon('pin', 18) ?> Delivery information</h2>
                </div>
                <div class="panel-body">
                    <div class="form-grid">
                        <div class="span-2">
                            <label class="form-label" for="address">Address</label>
                            <input type="text" id="address" name="address" class="form-control"
                                   value="<?= e($user['address']) ?>" autocomplete="street-address"
                                   placeholder="House or unit number, street, barangay">
                        </div>
                        <div>
                            <label class="form-label" for="province">Province</label>
                            <select id="province" name="province" class="form-select"
                                    autocomplete="address-level1" data-province>
                                <option value="">Select province</option>
                                <?php foreach (ph_provinces() as $prov): ?>
                                    <option value="<?= e($prov) ?>" <?= $currentProvince === $prov ? 'selected' : '' ?>>
                                        <?= e($prov) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="city">City / Municipality</label>
                            <select id="city" name="city" class="form-select"
                                    autocomplete="address-level2" data-city
                                    data-selected="<?= e($currentCity) ?>">
                                <?php if ($currentProvince === ''): ?>
                                    <option value="">Select a province first</option>
                                <?php else: ?>
                                    <option value="">Select city</option>
                                    <?php foreach (ph_cities($currentProvince) as $c): ?>
                                        <option value="<?= e($c) ?>" <?= $currentCity === $c ? 'selected' : '' ?>>
                                            <?= e($c) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="postal_code">Postal code</label>
                            <input type="text" id="postal_code" name="postal_code" class="form-control"
                                   value="<?= e($user['postal_code']) ?>" autocomplete="postal-code"
                                   inputmode="numeric" pattern="[0-9]{4,10}">
                        </div>
                    </div>
                    <p class="form-text">Used to pre-fill checkout. You can still change it on any order.</p>
                </div>
            </section>

            <div class="profile-actions">
                <!-- Locked view -->
                <button type="button" class="btn btn-primary" data-edit-start>
                    <?= icon('edit', 16) ?> Edit Profile
                </button>

                <!-- Edit view. Only this submit button posts the form. -->
                <button type="submit" class="btn btn-primary" data-edit-save>
                    <?= icon('check', 16) ?> Save Changes
                </button>
                <button type="button" class="btn btn-secondary" data-edit-cancel>Cancel</button>

                <p class="edit-hint" data-edit-hint>
                    Your details are locked. Press <strong>Edit Profile</strong> to make changes.
                </p>
            </div>
        </form>

        <?php if ($avatar): ?>
            <form method="post" data-confirm
                              data-confirm-title="Remove profile picture?"
                              data-confirm-body="Your initials will be shown instead."
                              data-confirm-note="You can upload a new one any time."
                              data-confirm-action="Remove">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="remove_avatar">
                <button type="submit" class="btn btn-danger-quiet btn-sm">
                    <?= icon('trash', 14) ?> Remove profile picture
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Server data for the city dropdown, not behaviour. An inert JSON block
     rather than executable script: profile.php re-validates whatever comes
     back against the same PHP list, so this is only a convenience. -->
<script type="application/json" id="phLocations">
    <?= json_encode($locationData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>
</script>

<?php require __DIR__ . '/../includes/customer/footer.php'; ?>
