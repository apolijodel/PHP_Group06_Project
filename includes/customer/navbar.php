<?php
$cartCount = cart_item_count();
$searchTerm = $_GET['q'] ?? '';
// Admins can close signups in Settings; when closed, no Register control is
// offered anywhere rather than leading guests to a refusal.
$registrationOpen = setting('allow_registration', '1') === '1';
?>
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header">
    <div class="container site-header-inner">
        <button type="button" class="btn-icon nav-toggle" id="navToggle"
                aria-expanded="false" aria-controls="primaryNav" aria-label="Open menu">
            <?= icon('menu') ?>
        </button>

        <a class="brand-mark" href="<?= BASE_URL ?>/customer/index.php">MarkMe</a>

        <nav id="primaryNav" aria-label="Primary">
            <ul class="site-nav">
                <?php
                /**
                 * One nav, five destinations. Our Story / How It Works / Shop /
                 * Contact are sections of the home page, so on the home page
                 * these are in-page jumps and script.js keeps the active state
                 * in sync with whatever is on screen. Everywhere else they are
                 * ordinary links back to that section, and the server marks the
                 * active item instead.
                 *
                 * data-nav-section is what the scrollspy binds to; "top" is the
                 * hero, which makes Home active at the top of the page.
                 */
                $onHome = basename($_SERVER['SCRIPT_NAME'] ?? '') === 'index.php';
                /**
                 * On the home page these are in-page anchors, so they scroll
                 * rather than reload. Home was the exception: its href had no
                 * fragment, so clicking it navigated to the same URL and the
                 * browser reloaded the whole page. The hero carries id="top",
                 * which is what makes "#top" a real destination.
                 *
                 * From any other page they stay absolute, so Home is a normal
                 * link and the back button, new-tab and refresh all behave.
                 */
                $home = $onHome ? '' : '/customer/index.php';
                $navItems = [
                    ['top', 'Home', $home . '#top'],
                    ['story', 'Our Story', $home . '#story'],
                    ['how-it-works', 'How It Works', $home . '#how-it-works'],
                    ['shop', 'Shop', $home . '#shop'],
                    ['contact', 'Contact', $home . '#contact'],
                ];
                // Product, cart and checkout pages are part of shopping, so Shop
                // stays lit while the customer is inside that flow.
                $shopFlow = ['shop.php', 'product.php', 'cart.php', 'checkout.php'];
                $activeKey = $onHome
                    ? 'top'
                    : (in_array(basename($_SERVER['SCRIPT_NAME'] ?? ''), $shopFlow, true) ? 'shop' : '');
                ?>
                <?php foreach ($navItems as [$key, $label, $href]): ?>
                    <li>
                        <a class="<?= $activeKey === $key ? 'active' : '' ?>"
                           data-nav-section="<?= e($key) ?>"
                           <?= $activeKey === $key ? 'aria-current="true"' : '' ?>
                           href="<?= $href[0] === '#' ? e($href) : BASE_URL . $href ?>"><?= e($label) ?></a>
                    </li>
                <?php endforeach; ?>
                <?php if (!is_logged_in()): ?>
                    <li class="nav-auth-only">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
                    </li>
                    <?php if ($registrationOpen): ?>
                        <li class="nav-auth-only">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal">Register</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="header-actions">
            <a class="btn-icon cart-link" href="<?= BASE_URL ?>/customer/cart.php"
               aria-label="Cart<?= $cartCount ? ', ' . $cartCount . ' item' . ($cartCount === 1 ? '' : 's') : ', empty' ?>">
                <?= icon('cart') ?>
                <?php if ($cartCount > 0): ?>
                    <span class="cart-count" aria-hidden="true"><?= $cartCount > 99 ? '99+' : $cartCount ?></span>
                <?php endif; ?>
            </a>

            <?php if (is_manager()): ?>
                <?php /* Beside the avatar rather than inside the menu: someone
                         who works in the back office moves between the two
                         sides constantly, and the counterpart "View site"
                         button already sits in the same spot over there. */ ?>
                <a class="btn btn-secondary btn-sm header-panel-link"
                   href="<?= BASE_URL . panel_base() ?>/dashboard.php">
                    <?= icon('dashboard', 15) ?>
                    <span><?= is_admin() ? 'Admin Panel' : 'Staff Panel' ?></span>
                </a>
            <?php endif; ?>

            <span class="divider-v" aria-hidden="true"></span>

            <?php if (is_logged_in()): ?>
                <?php
                $headerAvatar = avatar_url(current_user()['avatar_path'] ?? null);
                // Log Out is reachable only from inside this menu — there is no
                // standalone logout control beside the avatar any more.
                $accountLinks = [
                    ['profile.php', 'user', 'My Profile'],
                    ['orders.php', 'package', 'My Orders'],
                    ['my_designs.php', 'palette', 'Saved Designs'],
                    ['settings.php', 'settings', 'Settings'],
                ];
                ?>
                <div class="dropdown account-menu">
                    <button type="button" class="header-user" id="accountMenuBtn"
                            data-bs-toggle="dropdown" data-bs-offset="0,8" aria-expanded="false">
                        <?php if ($headerAvatar): ?>
                            <img class="avatar avatar-img" src="<?= e($headerAvatar) ?>" alt="" width="32" height="32">
                        <?php else: ?>
                            <span class="avatar" aria-hidden="true"><?= e(initials(current_user()['full_name'])) ?></span>
                        <?php endif; ?>
                        <span class="name"><?= e(current_user()['username']) ?></span>
                        <span class="caret" aria-hidden="true"><?= icon('chevron-down', 15) ?></span>
                        <span class="visually-hidden">Open account menu</span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end account-dropdown"
                         aria-labelledby="accountMenuBtn">
                        <div class="account-dropdown-id">
                            <?php if ($headerAvatar): ?>
                                <img class="avatar avatar-img" src="<?= e($headerAvatar) ?>" alt="" width="38" height="38">
                            <?php else: ?>
                                <span class="avatar" aria-hidden="true"><?= e(initials(current_user()['full_name'])) ?></span>
                            <?php endif; ?>
                            <span class="who">
                                <strong><?= e(current_user()['full_name']) ?></strong>
                                <span><?= e(current_user()['email']) ?></span>
                            </span>
                        </div>

                        <?php foreach ($accountLinks as [$file, $ico, $label]): ?>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/customer/<?= $file ?>">
                                <?= icon($ico, 17) ?> <?= e($label) ?>
                            </a>
                        <?php endforeach; ?>

                        <hr class="dropdown-divider">
                        <a class="dropdown-item is-logout" href="<?= BASE_URL ?>/customer/logout.php">
                            <?= icon('logout', 17) ?> Log Out
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <button type="button" class="btn btn-quiet btn-sm header-auth" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
                <?php if ($registrationOpen): ?>
                    <button type="button" class="btn btn-primary btn-sm header-auth" data-bs-toggle="modal" data-bs-target="#registerModal">Register</button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

</header>
