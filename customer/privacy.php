<?php
/**
 * Privacy policy.
 *
 * Written from what the code actually does. Every claim below is checkable
 * against the schema and the handlers - there is no third-party analytics,
 * no advertising, and no card storage to describe, because none exists.
 */
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Privacy Policy';
require __DIR__ . '/../includes/customer/header.php';
?>

<div class="account-page">
    <div class="page-head">
        <div>
            <p class="eyebrow">Legal</p>
            <h1 style="margin-top:var(--s-2)">Privacy Policy</h1>
            <p>What MarkMe stores, why, and what we do not collect.
                Last updated <?= e(date('F j, Y', filemtime(__FILE__))) ?>.</p>
        </div>
    </div>

    <div class="d-flex flex-column" style="gap:var(--s-5)">
        <section class="card card-flush">
            <div class="panel-head"><h2>What we collect</h2></div>
            <div class="panel-body">
                <ul class="perm-list is-allowed" style="margin:0">
                    <li><?= icon('user', 15) ?><span><strong>Your account</strong> — name, username,
                        email address, and an optional contact number and profile picture.</span></li>
                    <li><?= icon('truck', 15) ?><span><strong>Delivery details</strong> — the address,
                        city, province and postal code you give us, so orders can reach you.</span></li>
                    <li><?= icon('package', 15) ?><span><strong>Orders and designs</strong> — what you
                        ordered, the personalisation you chose, and any photo you uploaded for a
                        bookmark.</span></li>
                    <li><?= icon('shield', 15) ?><span><strong>Security records</strong> — sign-ins,
                        sign-outs, failed sign-in attempts and changes made in the back office,
                        together with the IP address the request came from.</span></li>
                </ul>
            </div>
        </section>

        <section class="card card-flush">
            <div class="panel-head"><h2>What we do not collect</h2></div>
            <div class="panel-body">
                <ul class="perm-list is-denied" style="margin:0">
                    <li><?= icon('close', 15) ?><span><strong>Card numbers.</strong> MarkMe offers cash
                        on delivery and bank transfer only. No card details are requested, processed
                        or stored anywhere in the system.</span></li>
                    <li><?= icon('close', 15) ?><span><strong>Your password.</strong> It is hashed with
                        bcrypt before it is stored, so it cannot be read back — not by us
                        either.</span></li>
                    <li><?= icon('close', 15) ?><span><strong>Advertising or analytics trackers.</strong>
                        There are none on this site.</span></li>
                </ul>
            </div>
        </section>

        <section class="card card-flush" id="cookies">
            <div class="panel-head"><h2>Cookies</h2></div>
            <div class="panel-body">
                <p style="margin-top:0">MarkMe sets <strong>one</strong> cookie:
                    <code>MARKMESESSID</code>, the session cookie that keeps you signed in as you move
                    between pages. It is marked <code>HttpOnly</code> so scripts cannot read it, and
                    <code>SameSite=Lax</code> so it is not sent along with requests from other sites.
                    It expires when you close your browser, or sooner if your session times out.</p>
                <p style="margin-bottom:0">Your theme choice and whether you have dismissed the cookie
                    notice are kept in your browser's local storage, not in a cookie, and never reach
                    our server.</p>
            </div>
        </section>

        <section class="card card-flush">
            <div class="panel-head"><h2>Who can see your information</h2></div>
            <div class="panel-body">
                <p style="margin-top:0">Staff can see orders and customer names in order to fulfil and
                    ship them. Administrators can additionally see account and security records.
                    Neither can see your password, and neither can see another customer's saved
                    designs.</p>
                <p style="margin-bottom:0">We do not sell your information, and we do not share it with
                    third parties.</p>
            </div>
        </section>

        <section class="card card-flush">
            <div class="panel-head"><h2>Your choices</h2></div>
            <div class="panel-body">
                <p style="margin-top:0">You can change your details at any time in
                    <a href="<?= BASE_URL ?>/customer/profile.php">Profile</a>, change your password or
                    turn on two-factor authentication in
                    <a href="<?= BASE_URL ?>/customer/settings.php">Settings</a>, and delete any saved
                    design from <a href="<?= BASE_URL ?>/customer/my_designs.php">Saved Designs</a>.</p>
                <p style="margin-bottom:0">To have your account removed, contact us from the
                    <a href="<?= BASE_URL ?>/customer/index.php#contact">contact form</a> and an
                    administrator will action it.</p>
            </div>
        </section>
    </div>
</div>

<?php require __DIR__ . '/../includes/customer/footer.php'; ?>
