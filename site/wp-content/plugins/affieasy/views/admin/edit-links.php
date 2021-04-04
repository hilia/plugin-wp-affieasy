<?php

use affieasy\AFES_DbManager;
use affieasy\AFES_Link;
use affieasy\AFES_LinkList;
use affieasy\AFES_Utils;

$pluginName = AFES_Utils::get_plugin_name();

require_once dirname(__DIR__, 3) . '/' . $pluginName . '/classes/class-afes-link-list.php';

$linkList = new AFES_LinkList();

wp_enqueue_style(
    'edit-links-style',
    plugins_url('/' . $pluginName . '/css/edit-links.css'),
    array(),
    time());

wp_enqueue_style('wp-jquery-ui-dialog');

wp_enqueue_script(
    'utils-script',
    plugins_url('/' . $pluginName . '/js/utils.js'),
    array(),
    time()
);

wp_enqueue_script(
    'edit-links-script',
    plugins_url('/' . $pluginName . '/js/edit-links.js'),
    array('jquery', 'utils-script', 'jquery-ui-dialog'),
    time()
);

wp_localize_script('edit-links-script', 'translations', array(
    'addNewLink' => esc_html__('Add new link', 'affieasy'),
    'add' => esc_html__('Add', 'affieasy'),
    'cancel' => esc_html__('Cancel', 'affieasy'),
    'yes' => esc_html__('Yes', 'affieasy'),
    'no' => esc_html__('No', 'affieasy'),
));

$dbManager = new AFES_DbManager();

$actionType = isset($_POST['actionType']) ? sanitize_key($_POST['actionType']) : null;
$id = isset($_POST['id']) && is_numeric($_POST['id']) ? intval(sanitize_key($_POST['id'])) : null;

if (isset($actionType)) {
    if ($actionType === 'deletion' && isset($id) && is_numeric($id)) {
        $dbManager->delete_link($id);
    } else if ($actionType === 'edition') {
        $dbManager->edit_link(new AFES_Link(
            $id,
            isset($_POST['webshopId']) ? sanitize_key($_POST['webshopId']) : null,
            isset($_POST['label']) ? sanitize_text_field($_POST['label']) : null,
            isset($_POST['parameters']) ? AFES_Utils::sanitize_parameters($_POST['parameters']) : null,
            isset($_POST['urlParam']) ? esc_url_raw(str_replace('[', '', str_replace(']', '', $_POST['urlParam']))) : null,
            isset($_POST['noFollow']) ? sanitize_key($_POST['noFollow']) === 'on' : false,
        ));
    }
}

$webshops = $dbManager->get_webshop_list();
?>

<div id="dialog-confirm-delete" title="<?php esc_html_e('Confirmation', 'affieasy'); ?>" hidden>
    <p>
        <?php esc_html_e('Are you sure you want to delete the link?', 'affieasy'); ?>
    </p>
</div>

<div id="edit-link-modal" hidden>
    <form id="form" class="validate" method="post">
        <input type="hidden" id="id" name="id" value="">
        <input type="hidden" id="actionType" name="actionType" value="edition">
        <input type="hidden" id="parameters" name="parameters" value="">
        <input type="hidden" id="urlParam" name="urlParam" value="">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="webshopId">
                        <?php esc_html_e('Webshop', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <select id="webshopId" name="webshopId" class="width-100">
                        <?php foreach ($webshops as $webshop) { ?>
                            <option
                                    value="<?php echo $webshop->getId(); ?>"
                                    data-url="<?php echo $webshop->getUrl(); ?>"
                                    data-parameters="<?php echo implode('|||', $webshop->getParameters()); ?>">
                                <?php echo $webshop->getName(); ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="label">
                        <?php esc_html_e('Link label', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <input
                            id="label"
                            name="label"
                            type="text"
                            class="label width-100"
                            maxlength="255"
                            value="">
                </td>
            </tr>
            <tr id="no-follow-row">
                <th scope="row">
                    <label for="noFollow">
                        <?php esc_html_e('No follow link', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <input type="checkbox" id="noFollow" name="noFollow" checked>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label>
                        <?php esc_html_e('Link overview', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <p id="p-overview"></p>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
</div>

<div class="wrap">
    <div class="header">
        <h1 class="wp-heading-inline"><?php echo esc_html__('Affiliate links', 'affieasy'); ?></h1>

        <button type="button" id="add-new-link" class="page-title-action">
            <?php esc_html_e('Add new link', 'affieasy'); ?>
        </button>
    </div>

    <hr class="wp-header-end">

    <?php if (isset($actionType)) { ?>
        <div id="message" class="notice notice-success is-dismissible">
            <p><strong>
                    <?php if ($actionType === 'deletion') {
                        esc_html_e('The link has been deleted', 'affieasy');
                    } else {
                        esc_html_e('New link added', 'affieasy');
                    }
                    ?>
                </strong>
            </p>
        </div>
    <?php } ?>

    <form method="GET">
        <?php
        $linkList->prepare_items();
        $linkList->display();
        ?>
    </form>
</div>