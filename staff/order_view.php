<?php
/**
 * Order details - staff panel.
 *
 * The screen itself lives in includes/screens (logic) and includes/views
 * (markup), shared with the administrator panel. This file is only the wrapper
 * that decides which shell the screen renders inside.
 */
require __DIR__ . '/../includes/backoffice/screens/order_view.php';
require __DIR__ . '/includes/staff_header.php';
require __DIR__ . '/../includes/backoffice/views/order_view.php';
require __DIR__ . '/includes/staff_footer.php';
