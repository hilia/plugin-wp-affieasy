<?php

use affieasy\AFES_Utils;
use affieasy\AFES_LinkList;

$pluginName = AFES_Utils::get_plugin_name();

require_once dirname(__DIR__, 3) . '/' . $pluginName . '/classes/class-afes-link-list.php';

$linkList = new AFES_LinkList();

wp_enqueue_script(
    'edit-link-script',
    plugins_url('/' . $pluginName . '/js/edit-links.js'),
    array('jquery'),
    time()
);

?>

<div class="wrap">
    <div class="header">
        <h1 class="wp-heading-inline"><?php echo esc_html__('Affiliate links', 'affieasy'); ?></h1>

        <button type="button" class="page-title-action">
            <?php esc_html_e('Add new link', 'affieasy'); ?>
        </button>
    </div>

    <hr class="wp-header-end">

    <form method="GET">
        <?php
        $linkList->prepare_items();
        $linkList->display();
        ?>
    </form>
</div>