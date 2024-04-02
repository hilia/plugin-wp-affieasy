<?php

use affieasy\AFES_Constants;
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
    'editLink' => esc_html__('Edit link', 'affieasy'),
    'copyToClipboard' => esc_html__('Copy to clipboard', 'affieasy'),
    'tagCopied' => esc_html__('Tag copied!', 'affieasy'),
    'shortUrlCopied' => esc_html__('Short url copied!', 'affieasy'),
    'add' => esc_html__('Add', 'affieasy'),
    'edit' => esc_html__('Edit', 'affieasy'),
    'cancel' => esc_html__('Cancel', 'affieasy'),
    'yes' => esc_html__('Yes', 'affieasy'),
    'no' => esc_html__('No', 'affieasy'),
));

$dbManager = new AFES_DbManager();

$actionType = isset($_REQUEST['actionType']) ? sanitize_key($_REQUEST['actionType']) : null;
$id = isset($_REQUEST['idParam']) && is_numeric($_REQUEST['idParam']) ? intval(sanitize_key($_REQUEST['idParam'])) : null;
$nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : null;
if (isset($actionType)) {
    
    if ($actionType === 'deletion' && isset($id) && is_numeric($id) && wp_verify_nonce( $nonce, 'my-nonce') ) {
        $dbManager->delete_link($id);
    } else if ($actionType === 'edition' && wp_verify_nonce( $nonce, 'edit-link-nonce') ) {
        $dbManager->edit_link(new AFES_Link(
            $id,
            isset($_POST['webshopIdParam']) ? sanitize_key($_POST['webshopIdParam']) : null,
            isset($_POST['labelParam']) ? sanitize_text_field($_POST['labelParam']) : null,
            isset($_POST['categoryParam']) ? sanitize_text_field($_POST['categoryParam']) : null,
            isset($_POST['parametersParam']) ? AFES_Utils::sanitize_parameters($_POST['parametersParam']) : null,
            isset($_POST['urlParam']) ? esc_url_raw(str_replace('[', '', str_replace(']', '', preg_replace('/\[[\s\S]+?]/', '', $_POST['urlParam'])))) : null,
            isset($_POST['noFollowParam']) ? sanitize_key($_POST['noFollowParam']) === 'on' : false,
            isset($_POST['openInNewTabParam']) ? sanitize_key($_POST['openInNewTabParam']) === 'on' : false
        ));
    }
}

$canUsePremiumCode = true;

$currentLinkCount = 0;
if (!$canUsePremiumCode) {
    $currentLinkCount = $dbManager->get_table_count(TABLE_LINK);
}

$webshops = $dbManager->get_webshop_list();
$hasNoWebshop = empty($webshops);
?>

<div id="dialog-confirm-delete" title="<?php esc_html_e('Confirmation', 'affieasy'); ?>" hidden>
    <p>
        <?php esc_html_e('Are you sure you want to delete the link?', 'affieasy'); ?>
    </p>
</div>

<div id="edit-link-modal" hidden>
    <h4><span class="dashicons dashicons-info"></span> <?php esc_html_e("If you don't use tags to display links, only urls parameters should be filled in.", 'affieasy'); ?></h4>
    <form id="form" class="validate" method="post">
        <input type="hidden" id="idParam" name="idParam" value="">
        <input type="hidden" id="actionType" name="actionType" value="edition">
        <input type="hidden" id="parametersParam" name="parametersParam" value="">
        <input type="hidden" id="urlParam" name="urlParam" value="">
        <?php wp_nonce_field('edit-link-nonce', '_wpnonce');?>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="webshopIdParam">
                        <?php esc_html_e('Webshop', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <select id="webshopIdParam" name="webshopIdParam" class="width-100">
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
                    <label for="labelParam">
                        <?php esc_html_e('Link label', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <input
                            id="labelParam"
                            name="labelParam"
                            type="text"
                            class="width-100"
                            maxlength="255"
                            value="">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="categoryParam">
                        <?php esc_html_e('Category', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <input
                            id="categoryParam"
                            name="categoryParam"
                            type="text"
                            class="width-100"
                            maxlength="255"
                            value="">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="openInNewTabParam">
                        <?php esc_html_e('Open in new tab', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <input type="checkbox" id="openInNewTabParam" name="openInNewTabParam" checked>
                </td>
            </tr>
            <tr id="no-follow-row">
                <th scope="row">
                    <label for="noFollowParam">
                        <?php esc_html_e('No follow link', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <input type="checkbox" id="noFollowParam" name="noFollowParam" checked>
                </td>
            </tr>
            <tr>
                <th scope="row" class="overview">
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

        <?php if ($hasNoWebshop) { ?>
            <?php require_once dirname(__DIR__, 3) . '/' . $pluginName . '/inc/afes-link-search-message.php'; ?>
            <h4>
                <span class="dashicons dashicons-info"></span>
                <span>
                    <?php esc_html_e('Add webshop to use this functionnality.', 'affieasy'); ?>
                </span>
            </h4>
        <?php } else if ($canUsePremiumCode || $currentLinkCount < 50) { ?>
            <button type="button" id="add-new-link" class="page-title-action">
                <?php esc_html_e('Add new link', 'affieasy'); ?>
            </button>
            <?php require_once dirname(__DIR__, 3) . '/' . $pluginName . '/inc/afes-link-search-message.php'; ?>
        <?php } else { ?>
            <?php require_once dirname(__DIR__, 3) . '/' . $pluginName . '/inc/afes-link-search-message.php'; ?>
            <h4>
                <span class="dashicons dashicons-info"></span>
                <span>
                    <?php esc_html_e('Buy a premium licence to create more than 50 links.', 'affieasy'); ?>
                </span>
            </h4>
        <?php } ?>
    </div>

    <hr class="wp-header-end">

    <?php if (isset($actionType)) { ?>
        <div id="message" class="notice notice-success is-dismissible">
            <p><strong>
                    <?php if ($actionType === 'deletion') {
                        esc_html_e('The link has been deleted', 'affieasy');
                    } else {
                        esc_html_e(isset($id) ? 'The link has been updated' : 'New link added', 'affieasy');
                    }
                    ?>
                </strong>
            </p>
        </div>
    <?php } ?>

    <form method="GET">
        <?php
        $linkList->prepare_items();
        $linkList->search_box(esc_html__('Search links', 'affieasy'), 'affieasy');
        $linkList->display();
        ?>
    </form>

    <div id="usage-info"><span class="dashicons dashicons-info"></span> <?php esc_html_e('Favor the use of tags to keep your links up to date in your pages and benefit from automatic generation.', 'affieasy'); ?></div>
</div>
<script>
    jQuery(($) => {

        $('.delete-link-confirm').click(function(e){
        
            if (!confirm('<?php esc_html_e('Are you sure you want to delete the link?', 'affieasy'); ?>')){
                e.preventDefault();
            }    

        });
    });
</script>