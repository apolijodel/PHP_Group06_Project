<?php
/**
 * Screen: order_view - server-side half.
 *
 * Auth, form handling and data loading, shared by the administrator
 * panel and the staff panel. Runs before either shell opens any
 * output, so redirect() still works.
 */
$requireCapability = 'orders.view';
$pageTitle = 'Order Details';
