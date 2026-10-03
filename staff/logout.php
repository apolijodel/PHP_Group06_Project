<?php
/**
 * Staff sign-out. Same effect as the administrator one; it exists so the
 * staff panel is self-contained and its links never leave /staff.
 */
require_once __DIR__ . '/../includes/auth.php';
logout_user();
redirect('/customer/index.php?signedout=1');
