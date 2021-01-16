<?php

require_once ABSPATH . '/wp-content/plugins/affieasy/classes/class-webshop-list.php';

wp_enqueue_style(
    'list-webshop-style',
    plugins_url('/affieasy/css/list-webshop.css'),
    array(),
    time());

wp_enqueue_script(
'list-webshop-script',
plugins_url('/affieasy/js/list-webshop.js'),
array('jquery', 'jquery-ui-dialog'),
time()
);

wp_enqueue_style('wp-jquery-ui-dialog');

$id = $_GET['id'];
$isValidDeleteAction = $_GET['action'] == 'delete-webshop' && is_numeric($id);
if ($isValidDeleteAction) {
    $dbManager = new DbManager();
    $dbManager->delete_webshop($id);
}

$webshopList = new WebshopList();

?>

<div id="dialog-confirm-delete" title="Confirmation" hidden>
    <p>
        Are you sure you want to delete the webshop (all related links will be removed)?
    </p>
</div>

<div class="wrap">

    <div class="header">
        <h1 class="wp-heading-inline">Webshops</h1>

        <a href="admin.php?page=affieasy-webshop&action=edit-webshop" class="page-title-action">
            Add new webshop
        </a>
    </div>

    <hr class="wp-header-end">

    <?php if ($isValidDeleteAction) { ?>
        <div class="notice notice-success settings-error is-dismissible">
            <p><strong>The webshop has been deleted</strong></p>
        </div>
    <?php } ?>

    <form method="GET">
        <?php
        $webshopList->prepare_items();
        $webshopList->display();
        ?>
    </form>
</div>