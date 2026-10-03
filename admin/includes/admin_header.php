<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/icons.php';
/**
 * /admin is the administrator panel. Staff have their own at /staff, which
 * renders the same screens from includes/views, so a staff member who lands
 * here is sent there rather than refused - the separation is about which
 * panel you work in, not about taking anything away.
 *
 * Capability checks still apply on top: being an administrator gets you into
 * the panel, not automatically into every screen.
 */
require_manager();

if (!is_admin()) {
    // Only for screens the staff panel actually has. Reports, Security and
    // the administrator-only account screens have no staff equivalent, so
    // those fall through to the capability check below and are refused
    // properly rather than redirected to a page that does not exist.
    //
    // Settings is a special case: /staff/settings.php exists, but it is a
    // different screen rather than the same one in another shell - it carries
    // the personal preferences only. This redirect never fires for it, because
    // admin/settings.php calls require_admin() before including this header
    // and refuses first. That is the right order: the page that writes store
    // configuration should say no on its own account, not rely on the shell.
    // Staff reach their own settings from the account menu.
    $staffEquivalent = __DIR__ . '/../../staff/' . basename($_SERVER['SCRIPT_NAME']);
    if (is_file($staffEquivalent)) {
        redirect('/staff/' . basename($_SERVER['SCRIPT_NAME']));
    }
}

if (isset($requireCapability)) {
    require_can($requireCapability);
}

require __DIR__ . '/../../includes/backoffice/shell_header.php';
