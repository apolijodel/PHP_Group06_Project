<?php
/**
 * Screen: customer_view - server-side half.
 *
 * Auth, form handling and data loading, shared by the administrator
 * panel and the staff panel. Runs before either shell opens any
 * output, so redirect() still works.
 */
/**
 * Customer detail — account information plus that customer's order history.
 *
 * The password hash is never selected into the page, so there is no way for it
 * to be rendered by accident.
 */
$requireCapability = 'customers.view';
$pageTitle = 'Customer';
