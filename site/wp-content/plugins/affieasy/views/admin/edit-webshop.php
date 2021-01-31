<?php

wp_enqueue_style(
    'edit-table-style',
    plugins_url('/affieasy/css/edit-webshop.css'),
    array(),
    time());

wp_enqueue_style(
    'color-picker-style',
    plugins_url('/affieasy/libs/color-picker/color-picker.css'),
    array(),
    time());

wp_register_script('color-picker', plugins_url('/affieasy/libs/color-picker/color-picker.min.js'));

wp_enqueue_script(
    'edit-webshop-script',
    plugins_url('/affieasy/js/edit-webshop.js'),
    array('jquery', 'jquery-ui-accordion', 'color-picker'),
    time()
);

$dbManager = new DbManager();

$webshop = new Webshop(
    isset($_POST['id']) ? $_POST['id'] : null,
    isset($_POST['name']) ? $_POST['name'] : null,
    isset($_POST['url']) ? $_POST['url'] : null,
    isset($_POST['link-text-preference']) ? $_POST['link-text-preference'] : null,
    isset($_POST['background-color-preference']) ? $_POST['background-color-preference'] : null,
    isset($_POST['text-color-preference']) ? $_POST['text-color-preference'] : null
);

$errors = array();

$id = isset($_GET['id']) ? $_GET['id'] : null;

$submit = isset($_POST['submit']) ? $_POST['submit'] : null;
$isFromSaveAction = $submit === 'save-action';
if ($isFromSaveAction) {
    if (empty($webshop->getName())) {
        array_push($errors, __('Name must not be empty', 'affieasy'));
    }

    $webshopUrl = $webshop->geturl();
    if (empty($webshopUrl)) {
        array_push($errors, __('Url must not be empty', 'affieasy'));
    } else {
        if (!in_array(Constants::MANDATORY_URL_PARAM, $webshop->getParameters())) {
            array_push($errors, sprintf(
                    __('Url must contains at least [[%1$s]] parameter', 'affieasy'),
                    Constants::MANDATORY_URL_PARAM));
        }
    }

    if (empty($errors)) {
        $webshop = $dbManager->edit_webshop($webshop);
    }
} else if (!empty($id)) {
    $webshop = $dbManager->get_webshop_by_id($id);
}

$webshopId = $webshop->getId();
$webshopName = $webshop->getName();

?>

<?php require_once ABSPATH . '/wp-content/plugins/affieasy/inc/free-version-message.php'; ?>
<div class="wrap">
    <div class="header">
        <h1 class="wp-heading-inline"><?php echo empty($webshopId) ?
                __('Create webshop', 'affieasy') :
                __('Update webshop', 'affieasy') . ' ' . $webshopName; ?></h1>
        <a href="admin.php?page=affieasy-webshop" class="page-title-action">
            <?php esc_html_e('Back to webshop list', 'affieasy'); ?>
        </a>
    </div>

    <hr class="wp-header-end">

    <?php if ($isFromSaveAction) {
        $hasErrors = !empty($errors);
        ?>
        <div
                id="setting-error-settings_updated"
                class="notice notice-<?php echo $hasErrors ? 'error' : 'success' ?> is-dismissible">
            <?php if ($hasErrors) {
                foreach ($errors as $error) { ?>
                    <p><strong><?php echo $error; ?></strong></p>
                <?php }
            } else { ?>
                <p><strong><?php printf(esc_html__('Webshop %1$s saved', 'affieasy'), $webshopName); ?></strong></p>
            <?php } ?>
        </div>
    <?php } ?>

    <div id="helper">
        <div type="button" id="helper-title">
            <?php esc_html_e('How to use this plugin?', 'affieasy'); ?>
            <span id="helper-icon" class="dashicons dashicons-arrow-down"></span>
        </div>
        <div id="helper-content">
            <?php esc_html_e(
                    'If you want to manage affiate links in your tables, the best way with this plugin is to use webshops.',
                    'affieasy'); ?>
            <br>

            <?php esc_html_e('A webshop represents a website like amazon, alibaba,...', 'affieasy'); ?>
            <br><br>

            <?php esc_html_e('Let\'s take a simple example to understand how the plugin work:', 'affieasy'); ?><br>
            <?php esc_html_e(
                    'You want to have affiliate links in your tables for decathlon webshop which use awin as advertising agency.',
                    'affieasy'); ?>
            <br><br>

            <?php esc_html_e('You just have to create a new webshop decathlon with this url:', 'affieasy'); ?><br>
            <strong>https://www.awin1.com/cread.php?p=[[product_url]]&clickref=[[click_ref]]</strong><br>
            <strong>[[product_url]]</strong> <?php esc_html_e('and', 'affieasy'); ?> <strong>[[click_ref]]</strong> <?php esc_html_e('will be variable parameters', 'affieasy'); ?>
            <strong><?php esc_html_e('(at least, product_url have to be specified)','affieasy'); ?></strong> <?php esc_html_e('but you can add others as many as needed','affieasy'); ?>
            <?php esc_html_e('between', 'affieasy'); ?> <strong>[[</strong> <?php esc_html_e('and', 'affieasy'); ?> <strong>]]</strong>.<br><br>

            <?php esc_html_e(
                    'Now You can add affiliate links for decathlon in your tables (edit table -> add row -> Affiliate links -> select decathlon). Parameters',
                    'affieasy'); ?>
            <strong>[[product_url]]</strong> <?php esc_html_e('and', 'affieasy'); ?> <strong>[[click_ref]]</strong>
            <?php esc_html_e('will be asked during creation process and the url will be automatically generated.', 'affieasy'); ?>
            <br><br>

            <!--If one day, the advertising agency changes for this webshop (it becomes Affilae), just update the url :
            <strong>[[product_url]]#ae1234&utm_source=affilae&clickref=[[click_ref]]</strong> and click on the
            "Update affiliate links for this shop" button.<br>
            A script will automatically update all table cells which contains decathlon links.<br><br>
            <span id="helper-warning" class="dashicons dashicons-warning"></span>If you add a new parameter in the
            updated url, you will have to update manually your links for this shop. -->
        </div>
    </div>

    <form id="form" class="validate" method="post">
        <input type="hidden" id="id" name="id" value="<?php echo $webshopId; ?>">
        <table class="form-table" role="presentation">
            <tr class="form-field">
                <th scope="row">
                    <label for="name">
                        <?php esc_html_e('Name', 'affieasy'); ?>
                        <span class="description"><?php esc_html_e('(Required)', 'affieasy'); ?></span>
                    </label>
                </th>
                <td>
                    <input
                            type="text"
                            name="name"
                            id="name"
                            maxlength="255"
                            value="<?php echo $webshopName; ?>">
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row">
                    <label for="url">
                        <?php esc_html_e('Affiliation url', 'affieasy'); ?>
                        <span class="description"><?php esc_html_e('(Required)', 'affieasy'); ?></span>
                    </label>
                </th>
                <td>
                    <input
                            type="text"
                            name="url"
                            id="url"
                            maxlength="2048"
                            value="<?php echo $webshop->getUrl(); ?>"
                            placeholder="<?php esc_html_e('Ex: https://www.awin1.com/cread.php?p=[[product_url]]&clickref=[[click_ref]]', 'affieasy'); ?>">
                </td>
            </tr>
        </table>

        <h2 class="title">
            <?php esc_html_e('Affiliate link preferences', 'affieasy'); ?>
            <span
                    class="dashicons dashicons-info"
                    title="<?php esc_html_e('If you fill preferences, affiliate links fields will be automatically entered during the creation process (it will be still possible to change values)', 'affieasy'); ?>">
            </span>
        </h2>

        <table class="form-table" role="presentation">
            <tr class="form-field">
                <th scope="row">
                    <label for="link-text-preference">
                        <?php esc_html_e('Link text', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <input
                            type="text"
                            name="link-text-preference"
                            id="link-text-preference"
                            maxlength="255"
                            value="<?php echo $webshop->getLinkTextPreference(); ?>">
                </td>
            </tr>

            <tr class="form-field">
                <th scope="row">
                    <label for="background-color-preference">
                        <?php esc_html_e('Background color', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <input
                            type="text"
                            id="background-color-preference"
                            name="background-color-preference"
                            value="<?php echo $webshop->getBackgroundColorPreference(); ?>">
                </td>
            </tr>

            <tr class="form-field">
                <th scope="row">
                    <label for="text-color-preference">
                        <?php esc_html_e('Text color', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <input
                            type="text"
                            id="text-color-preference"
                            name="text-color-preference"
                            value="<?php echo $webshop->getTextColorPreference(); ?>">
                </td>
            </tr>
        </table>
    </form>

    <button
            type="submit"
            form="form"
            name="submit"
            id="submit"
            class="button button-primary edit-button-bottom"
            value="save-action">
        <?php esc_html_e('Save webshop', 'affieasy'); ?>
    </button>
</div>