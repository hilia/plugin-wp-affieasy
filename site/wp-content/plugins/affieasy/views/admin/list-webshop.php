<?php

$pluginName = Utils::get_plugin_name();

require_once ABSPATH . '/wp-content/plugins/' . $pluginName . '/classes/class-webshop-list.php';

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

$id = isset($_GET['id']) ? $_GET['id'] : null;
$action = isset($_GET['action']) ? $_GET['action'] : null;

$isValidDeleteAction = $action === 'delete-webshop' && is_numeric($id);
if ($isValidDeleteAction) {
    $dbManager = new DbManager();
    $dbManager->delete_webshop($id);
}

$webshopList = new WebshopList();
?>

<div id="dialog-confirm-delete" title="<?php esc_html_e('Confirmation', 'affieasy'); ?>" hidden>
    <p>
        <?php esc_html_e('Are you sure you want to delete the webshop (all related links will be removed)?', 'affieasy'); ?>
    </p>
</div>

<div class="wrap">

    <div class="header">
        <?php require_once ABSPATH . '/wp-content/plugins/' . $pluginName . '/inc/free-version-message.php'; ?>
        <h1 class="wp-heading-inline"><?php esc_html_e('Webshops', 'affieasy'); ?></h1>

        <a href="admin.php?page=affieasy-webshop&action=edit-webshop" class="page-title-action">
            <?php esc_html_e('Add new webshop', 'affieasy'); ?>
        </a>
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
