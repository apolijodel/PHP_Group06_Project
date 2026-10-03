<?php
require_once __DIR__ . '/../includes/auth.php';
logout_user();
redirect('/customer/index.php?signedout=1');
