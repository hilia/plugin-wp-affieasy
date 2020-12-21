<?php

require_once dirname(__DIR__) . '/classes/class-table-list.php';

wp_enqueue_script(
    'list-table-script',
    plugins_url('/affieasy/js/list-table.js'),
    array('jquery', 'jquery-ui-dialog'),
    time()
);

wp_enqueue_style('wp-jquery-ui-dialog');

$id = $_GET['id'];
$isValidDeleteAction = $_GET['action'] == 'delete-table' && is_numeric($id);
if ($isValidDeleteAction) {
    $dbManager = new DbManager();
    $dbManager->delete_table($id);
}

$tableList = new TableList();
?>

<div id="dialog-confirm-delete" title="Confirmation" hidden>
    <p>
        Are you sure you want to delete the table?
    </p>
</div>

<div class="wrap">
    <h1 class="wp-heading-inline">Tables</h1>

    <a href="admin.php?page=affieasy-table&action=edit-table" class="page-title-action">
        Add new table
    </a>

    <hr class="wp-header-end">

    <?php if ($isValidDeleteAction) { ?>
        <div class="notice notice-success settings-error is-dismissible">
            <p><strong>The table has been deleted</strong></p>
        </div>
    <?php } ?>

    <form method="GET">
        <?php
        $tableList->prepare_items();
        $tableList->display();
        ?>
    </form>
</div>