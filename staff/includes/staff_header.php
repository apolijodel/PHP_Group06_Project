<?php
/**
 * Staff panel shell.
 *
 * Thin on purpose: the back-office chrome already brands itself from the
 * signed-in role and the sidebar already filters itself by capability, so
 * this reuses that rather than growing a second copy of the same markup.
 * What /staff gives you that /admin did not is a panel of its own - staff
 * links stay in /staff, and /admin is now administrators only.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/icons.php';

// Staff and administrators both pass; the per-screen capability decides the
// rest. An administrator following a /staff link is not an error.
require_manager();
if (isset($requireCapability)) {
    require_can($requireCapability);
}

require __DIR__ . '/../../includes/backoffice/shell_header.php';
