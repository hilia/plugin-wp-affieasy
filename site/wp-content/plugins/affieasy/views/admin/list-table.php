<?php

use affieasy\AFES_DbManager;
use affieasy\AFES_TableList;
use affieasy\AFES_Utils;

$pluginName = AFES_Utils::get_plugin_name();

require_once dirname(__DIR__, 3) . '/' . $pluginName . '/classes/class-afes-table-list.php';

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

$id = isset($_REQUEST['id']) ? sanitize_key($_REQUEST['id']) : null;
$action = isset($_REQUEST['action']) ?  sanitize_key($_REQUEST['action']) : null;

$isValidDeleteAction = $action === 'delete-table' && is_numeric($id);

if (is_numeric($id)) {
    $dbManager = new AFES_DbManager();
    
    $nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : null;

    if ($action === 'delete-table' && wp_verify_nonce( $nonce, 'my-nonce') ) {
        $dbManager->delete_table($id);
    } else if ($action === 'duplicate-table' && wp_verify_nonce( $nonce, 'my-nonce')) {
        $dbManager->duplicate_table($id);
    }
}

$tableList = new AFES_TableList();
?>

<div id="dialog-confirm-delete" title="<?php esc_html_e('Confirmation', 'affieasy'); ?>" hidden>
    <p>
        <?php esc_html_e('Are you sure you want to delete the table?', 'affieasy'); ?>
    </p>
</div>

<div class="wrap">
    
    <h1 class="wp-heading-inline"><?php esc_html_e('Tables', 'affieasy'); ?></h1>

    <a href="admin.php?page=affieasy-table&action=edit-table" class="page-title-action">
        <?php esc_html_e('Add new table', 'affieasy'); ?>
    </a>
    <hr class="wp-header-end">

    <?php if (isset($action)) { ?>
        <div class="notice notice-success settings-error is-dismissible">
            <p><strong><?php esc_html_e($action === 'delete-table' ?
                        'The table has been deleted' :
                        'The table has been duplicated', 'affieasy'); ?></strong></p>
        </div>
    <?php } ?>

    <form method="GET">
        <?php
        $tableList->prepare_items();
        $tableList->display();
        ?>
    </form>
</div>
<script>
    jQuery(($) => {

        $('.delete-table-confirm').click(function(e){
        
            if (!confirm('<?php esc_html_e('Are you sure you want to delete the table?', 'affieasy'); ?>')){
                e.preventDefault();
            }    

        });
    });
</script>