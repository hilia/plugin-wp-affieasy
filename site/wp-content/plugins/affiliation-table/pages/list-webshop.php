<?php
require_once dirname(__DIR__) . '/classes/class-webshop-list.php';

wp_enqueue_script(
'list-webshop-script',
plugins_url('/affiliation-table/js/list-webshop.js'),
array('jquery', 'jquery-ui-dialog'),
time()
);

$webshopList = new WebshopList();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Webshops</h1>

    <a href="admin.php?page=affiliation-table-webshop&action=edit-webshop" class="page-title-action">
        Add new webshop
    </a>

    <hr class="wp-header-end">

    <form method="GET">
        <?php
        $webshopList->prepare_items();
        $webshopList->display();
        ?>
    </form>
</div>