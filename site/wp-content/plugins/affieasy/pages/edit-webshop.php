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
    $_POST['id'],
    $_POST['name'],
    $_POST['url'],
    $_POST['link-text-preference'],
    $_POST['background-color-preference'],
    $_POST['text-color-preference']
);

$errors = array();

$id = $_GET['id'];
$isFromSaveAction = $_POST['submit'] == 'save-action';
if ($isFromSaveAction) {
    if (empty($webshop->getName())) {
        array_push($errors, 'Name must not be empty');
    }

    $webshopUrl = $webshop->geturl();
    if (empty($webshopUrl)) {
        array_push($errors, 'Url must not be empty');
    } else {
        if (!in_array(Constants::MANDATORY_URL_PARAM, $webshop->getParameters())) {
            array_push($errors, 'Url must contains at least [[' . Constants::MANDATORY_URL_PARAM . ']] parameter');
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

<div class="wrap">
    <div class="header">
        <h1 class="wp-heading-inline"><?php echo empty($webshopId) ? 'Create webshop' : 'Update webshop ' . $webshopName; ?></h1>
        <a href="admin.php?page=affieasy-webshop" class="page-title-action">
            Back to webshop list
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
                <p><strong>Webshop <?php echo $webshopName; ?> saved</strong></p>
            <?php } ?>
        </div>
    <?php } ?>

    <div id="helper">
        <div type="button" id="helper-title">
            What and how to use this tool?
            <span id="helper-icon" class="dashicons dashicons-arrow-down"></span>
        </div>
        <div id="helper-content">
            If you want to manage affiate links in your tables, the best way with this plugin is to use webshops.<br>
            A webshop represents a website like amazon, alibaba,...<br><br>

            Let's take a simple example to understand how the plugin work.<br>
            You want to have affiliate links in your tables for decathlon webshop which use awin as advertising agency.
            <br><br>

            You just have to create a new webshop decathlon with this url :<br>
            <strong>https://www.awin1.com/cread.php?p=[[product_url]]&clickref=[[click_ref]]</strong><br>
            <strong>[[product_url]]</strong> and <strong>[[click_ref]]</strong> will be variable parameters
            <strong>(at least, product_url have to be specified)</strong> but you can add others as many as needed
            between <strong>[[</strong> and <strong>]]</strong>.<br><br>

            Now You can add affiliate links for decathlon in your tables (edit table -> add row -> Affiliate links ->
            select decathlon). Parameters <strong>[[product_url]]</strong> and <strong>[[click_ref]]</strong> will be
            asked during creation process and the url will be automatically generated.<br><br>

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
                        Name
                        <span class="description">(required)</span>
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
                        Affiliation url
                        <span class="description">(required)</span>
                    </label>
                </th>
                <td>
                    <input
                            type="text"
                            name="url"
                            id="url"
                            maxlength="2048"
                            value="<?php echo $webshop->getUrl(); ?>"
                            placeholder="Ex: https://www.awin1.com/cread.php?p=[[product_url]]&clickref=[[click_ref]]">
                </td>
            </tr>
        </table>

        <h2 class="title">
            Affiliate link preferences
            <span
                    class="dashicons dashicons-info"
                    title="If you fill preferences, affiliate links fields will be automatically entered during the creation process (it will be still possible to change values)">
            </span>
        </h2>

        <table class="form-table" role="presentation">
            <tr class="form-field">
                <th scope="row">
                    <label for="link-text-preference">
                        Link text
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
                        Background color
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
                        Text color
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
        Save webshop
    </button>
</div>