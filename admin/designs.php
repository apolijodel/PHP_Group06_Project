<?php
$requireCapability = 'options.manage';
$pageTitle = 'Designs';
require __DIR__ . '/includes/admin_header.php';

$opt = [
    'table' => 'designs',
    'idColumn' => 'design_id',
    'folder' => 'designs',
    'singular' => 'Design',
    'plural' => 'Bookmark designs',
    'blurb' => 'Step 2 of the design studio — the base designs customers can pick from.',
    'saveAction' => 'design_save.php',
    'deleteAction' => 'design_delete.php',
    'icon' => 'palette',
];
require __DIR__ . '/includes/option_manager.php';

require __DIR__ . '/../includes/backoffice/shell_footer.php';
