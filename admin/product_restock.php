<?php
/**
 * Restock - administrator panel.
 *
 * The screen itself lives in includes/screens (logic) and includes/views
 * (markup), shared with the staff panel. This file is only the wrapper
 * that decides which shell the screen renders inside.
 */
require __DIR__ . '/../includes/backoffice/screens/product_restock.php';
require __DIR__ . '/includes/admin_header.php';
require __DIR__ . '/../includes/backoffice/views/product_restock.php';
require __DIR__ . '/../includes/backoffice/shell_footer.php';
