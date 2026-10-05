<?php
$requireCapability = 'options.manage';
$pageTitle = 'Shapes';
require __DIR__ . '/includes/admin_header.php';

$opt = [
    'table' => 'shapes',
    'idColumn' => 'shape_id',
    'folder' => 'shapes',
    'singular' => 'Shape',
    'plural' => 'Bookmark shapes',
    'blurb' => 'Step 1 of the design studio — the silhouettes customers can pick from.',
    'saveAction' => 'shape_save.php',
    'deleteAction' => 'shape_delete.php',
    'icon' => 'shapes',
    /* A controlled vocabulary, not free text: the point of asking is that
       the answers can be compared and filtered later. */
    'categories' => ['Classic', 'Animal', 'Nature', 'Geometric', 'Seasonal', 'Novelty'],
];
require __DIR__ . '/includes/option_manager.php';

require __DIR__ . '/../includes/backoffice/shell_footer.php';
