<?php
// Site root — customer pages live under /customer, admin pages under /admin.
require_once __DIR__ . '/includes/functions.php';
redirect('/customer/index.php');
