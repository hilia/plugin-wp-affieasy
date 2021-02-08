<?php

$pluginName = Utils::get_plugin_name();

require_once ABSPATH . '/wp-content/plugins/' . $pluginName . '/classes/class-table-list.php';

wp_enqueue_script(
    'list-table-script',
    plugins_url('/' . $pluginName . '/js/list-table.js'),
    array('jquery', 'jquery-ui-dialog'),
    time()
);

wp_localize_script( 'list-table-script', 'translations', array(
    'yes' => esc_html__('Yes', 'affieasy'),
    'no' => esc_html__('No', 'affieasy'),
));

wp_enqueue_style('wp-jquery-ui-dialog');

$id = isset($_GET['id']) ? $_GET['id'] : null;
$action = isset($_GET['action']) ?  $_GET['action'] : null;

$isValidDeleteAction = $action === 'delete-table' && is_numeric($id);
if ($isValidDeleteAction) {
    $dbManager = new DbManager();
    $dbManager->delete_table($id);
}

$tableList = new TableList();
?>

<div id="dialog-confirm-delete" title="<?php esc_html_e('Confirmation', 'affieasy'); ?>" hidden>
    <p>
        <?php esc_html_e('Are you sure you want to delete the table?', 'affieasy'); ?>
    </p>
</div>

<div class="wrap">
    <?php require_once ABSPATH . '/wp-content/plugins/' . $pluginName . '/inc/free-version-message.php'; ?>
    <h1 class="wp-heading-inline"><?php esc_html_e('Tables', 'affieasy'); ?></h1>

    <a href="admin.php?page=affieasy-table&action=edit-table" class="page-title-action">
        <?php esc_html_e('Add new table', 'affieasy'); ?>
    </a>
    <hr class="wp-header-end">

    <?php if ($isValidDeleteAction) { ?>
        <div class="notice notice-success settings-error is-dismissible">
            <p><strong><?php esc_html_e('The table has been deleted', 'affieasy'); ?></strong></p>
        </div>
    <?php } ?>

    <form method="GET">
        <?php
        $tableList->prepare_items();
        $tableList->display();
        ?>
    </form>
</div>