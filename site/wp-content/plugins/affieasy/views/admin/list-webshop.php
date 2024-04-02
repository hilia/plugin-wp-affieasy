<?php

use affieasy\AFES_DbManager;
use affieasy\AFES_Utils;
use affieasy\AFES_WebshopList;
use affieasy\AFES_Constants;

$pluginName = AFES_Utils::get_plugin_name();

require_once dirname(__DIR__, 3) . '/' . $pluginName . '/classes/class-afes-webshop-list.php';

wp_enqueue_style(
    'list-webshop-style',
    plugins_url('/' . $pluginName . '/css/list-webshop.css'),
    array(),
    time());

wp_enqueue_script(
    'list-webshop-script',
    plugins_url('/' . $pluginName . '/js/list-webshop.js'),
    array('jquery', 'jquery-ui-dialog'),
    time()
);

wp_localize_script( 'list-webshop-script', 'translations', array(
    'yes' => esc_html__('Yes', 'affieasy'),
    'no' => esc_html__('No', 'affieasy'),
));

wp_enqueue_style('wp-jquery-ui-dialog');

$dbManager = new AFES_DbManager();

$id = isset($_GET['id']) ? sanitize_key($_GET['id']) : null;
$action = isset($_GET['action']) ? sanitize_key($_GET['action']) : null;
$nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : null;

$isValidDeleteAction = $action === 'delete-webshop' && is_numeric($id);
if ($isValidDeleteAction  && wp_verify_nonce( $nonce, 'my-nonce')) {
    $dbManager->delete_webshop($id);
}

$canUsePremiumCode = true;

$dbManager = new AFES_DbManager();
$currentWebshopCount = 0;
if (!$canUsePremiumCode) {
    $currentWebshopCount = $dbManager->get_table_count(TABLE_WEBSHOP);
}

$webshopList = new AFES_WebshopList();
?>

<div id="dialog-confirm-delete" title="<?php esc_html_e('Confirmation', 'affieasy'); ?>" hidden>
    <p>
        <?php esc_html_e('Are you sure you want to delete the webshop (all related links will be removed)?', 'affieasy'); ?>
    </p>
</div>

<div class="wrap">

    <div class="header">
        
        <h1 class="wp-heading-inline"><?php esc_html_e('Webshops', 'affieasy'); ?></h1>

        <?php if ($canUsePremiumCode || $currentWebshopCount < 2) { ?>
            <a href="admin.php?page=affieasy-webshop&action=edit-webshop" class="page-title-action">
                <?php esc_html_e('Add new webshop', 'affieasy'); ?>
            </a>
        <?php } else { ?>
            <h4>
                <span class="dashicons dashicons-info"></span>
                <span>
                    <?php esc_html_e('Buy a premium licence to create more than 2 webshops', 'affieasy'); ?>
                </span>
            </h4>
        <?php } ?>
    </div>

    <hr class="wp-header-end">

    <?php if ($isValidDeleteAction) { ?>
        <div class="notice notice-success settings-error is-dismissible">
            <p><strong><?php esc_html_e('The webshop has been deleted', 'affieasy'); ?></strong></p>
        </div>
    <?php } ?>

    <form method="GET">
        <?php
        $webshopList->prepare_items();
        $webshopList->display();
        ?>
    </form>
</div>
<script>
    jQuery(($) => {

        $('.delete-webshop-confirm').click(function(e){
        
            if (!confirm('<?php esc_html_e('Are you sure you want to delete the webshop (all related links will be removed)?', 'affieasy'); ?>')){
                e.preventDefault();
            }    

        });
    });
</script>