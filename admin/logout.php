<?php
/**
 * Administrator sign-out.
 *
 * Lands on the storefront rather than /admin/login.php: signing out should put
 * you on the site, not on a locked door into the panel you just left. The
 * marker tells the storefront to open its sign-in dialog, and tells the theme
 * bootstrap to forget the stored dark-mode choice, so the machine is handed
 * back in the state a new visitor would find it.
 */
require_once __DIR__ . '/../includes/auth.php';
logout_user();
redirect('/customer/index.php?signedout=1');
