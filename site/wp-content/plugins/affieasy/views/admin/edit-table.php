<?php

use affieasy\AFES_DbManager;
use affieasy\AFES_GenerationUtils;
use affieasy\AFES_Table;
use affieasy\AFES_Utils;
use affieasy\AFES_Constants;

$pluginName = AFES_Utils::get_plugin_name();

wp_enqueue_style(
    'edit-table-style',
    plugins_url('/' . $pluginName . '/css/edit-table.css'),
    array(),
    time());

wp_enqueue_style(
    'color-picker-style',
    plugins_url('/' . $pluginName . '/libs/color-picker/color-picker.css'),
    array(),
    time());

wp_enqueue_style(
    'popover-modal-style',
    plugins_url('/' . $pluginName . '/libs/pop-modal/pop-modal.min.css'),
    array(),
    time());

wp_enqueue_style('wp-jquery-ui-dialog');

wp_register_script('color-picker', plugins_url('/' . $pluginName . '/libs/color-picker/color-picker.min.js'));
wp_register_script('pop-modal', plugins_url('/' . $pluginName . '/libs/pop-modal/pop-modal.min.js'), array('jquery'));
wp_register_script('table-dragger', plugins_url('/' . $pluginName . '/libs/table-dragger/table-dragger.min.js'));

wp_enqueue_script(
    'utils-script',
    plugins_url('/' . $pluginName . '/js/utils.js'),
    array(),
    time()
);

wp_enqueue_script(
    'edit-table-script',
    plugins_url('/' . $pluginName . '/js/edit-table.js'),
    array('jquery', 'utils-script', 'color-picker', 'pop-modal', 'table-dragger', 'jquery-ui-dialog'),
    time()
);

$canUsePremiumCode = true;

wp_localize_script( 'edit-table-script', 'translations', array(
    'add' => esc_html__('Add', 'affieasy'),
    'addAffliateLink' => esc_html__('Add affiliate link', 'affieasy'),
    'addColumnAfterThisOne' => esc_html__('Add a column after this one', 'affieasy'),
    'addRowAfterThisOne' => esc_html__('Add a row after this one', 'affieasy'),
    'cancel' => esc_html__('Cancel', 'affieasy'),
    'close' => esc_html__('Close', 'affieasy'),
    'createAffiliationLink' => esc_html__('Create affiliation link', 'affieasy'),
    'delete'  => esc_html__('Delete', 'affieasy'),
    'deleteColumn'  => esc_html__('Delete column', 'affieasy'),
    'deleteRow'  => esc_html__('Delete row', 'affieasy'),
    'dragAndDropColumn' => esc_html__($canUsePremiumCode ?
        'Keep the mouse pressed to drag and drop the column' :
        'Get the premium version to drag and drop the column', 'affieasy'),
    'dragAndDropRow' => esc_html__($canUsePremiumCode ?
        'Keep the mouse pressed to drag and drop the row' :
        'Get the premium version to drag and drop the row', 'affieasy'),
    'edit' => esc_html__('Edit', 'affieasy'),
    'editAffiliateLink' => esc_html__('Edit affiliate link', 'affieasy'),
    'editAffiliationLink' => esc_html__('Edit affiliation link', 'affieasy'),
    'editHeaderOptions' => esc_html__('Edit header options', 'affieasy'),
    'removeImage' => esc_html__('Remove image', 'affieasy'),
    'selectImage' => esc_html__('Select image', 'affieasy'),
    'selectOrUploadImage' => esc_html__('Select or Upload new image', 'affieasy'),
    'unknownType' => esc_html__('Unknown type', 'affieasy'),
    'validate' => esc_html__('Validate', 'affieasy'),
));

wp_enqueue_media();

$table = new AFES_Table(
    isset($_POST['id']) ? sanitize_key($_POST['id']) : null,
    isset($_POST['name']) ? sanitize_text_field($_POST['name']) : null,
    isset($_POST['header-type']) ? sanitize_text_field($_POST['header-type']) : null,
    isset($_POST['header-options']) ? AFES_Utils::sanitize_header_options($_POST['header-options']) : null,
    isset($_POST['content']) ? AFES_Utils::sanitize_content($_POST['content']) : null,
    isset($_POST['responsive-breakpoint']) ? sanitize_key($_POST['responsive-breakpoint']) : null,
    isset($_POST['max-width']) ? sanitize_key($_POST['max-width']) : null,
    isset($_POST['background-color']) ? sanitize_hex_color($_POST['background-color']) : null
);

$errors = array();
$dbManager = new AFES_DbManager();
$webshops = $dbManager->get_webshop_list();
$hasNoWebShop = empty($webshops);

$submit = isset($_POST['submit']) ? sanitize_key($_POST['submit']) : null;
$isFromSaveAction = $submit === 'save-action';

if ($isFromSaveAction) {

    $nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : null;
    if (wp_verify_nonce( $nonce, 'edit-table-nonce')){

        if (empty($table->getName())) {
            array_push($errors, esc_html__('Name must not be empty', 'affieasy'));
        }

        $isNullTableContent = $table->getContent() == null;
        $isTableWithColumnHeader = in_array($table->getHeaderType(), array('COLUMN_HEADER', 'BOTH'));
        $tableContentSize = $isNullTableContent ? 0 : count($table->getContent());

        if ($isTableWithColumnHeader && $tableContentSize < 2 || !$isTableWithColumnHeader && $tableContentSize < 1) {
            array_push($errors, esc_html__('Table must contains at least one row', 'affieasy'));
        }

        $responsiveBreakpoint = $table->getResponsiveBreakpoint();
        if ($responsiveBreakpoint !== '' && (!is_numeric($responsiveBreakpoint) || $responsiveBreakpoint < 0)) {
            array_push($errors, esc_html__('Responsive breakpoint must be a positive number', 'affieasy'));
        }

        $maxWidth = $table->getMaxWidth();
        if ($maxWidth !== '' && (!is_numeric($maxWidth) || $maxWidth < 0)) {
            array_push($errors, esc_html__('Max width must be a positive number', 'affieasy'));
        }

        if (count($errors) == 0) {
            $table = $dbManager->edit_table($table);
        } else {
            if ($isNullTableContent) {
                $table->initDefaultContent();
            }
        }
    
    } // fin check nonce

} else {
    $id = isset($_GET['id']) ? sanitize_key($_GET['id']) : null;
    if (!empty($id)) {
        $table = $dbManager->get_table_by_id($id);
    }

    if (empty($table->getId())) {
        $table->initDefaultContent();
    }
}

$firstRow = $table->getContent()[0];

$tableId = $table->getId();
$tableName = $table->getName();
$headerType = $table->getHeaderType();
$headerOptions = $table->getHeaderOptions();
$hasHeaderOptions = !empty($headerOptions);

$isTableWithColumnHeader = in_array($headerType, array('COLUMN_HEADER', 'BOTH'));
$isTableWithRowHeader = in_array($headerType, array('ROW_HEADER', 'BOTH'));

$isFromSaveActionOrNotNew = $isFromSaveAction || !empty($table->getId());
?>

<div id="edit-affiliation-link-modal" hidden>
    <?php if ($hasNoWebShop) { ?>
        <p>
            <span class="dashicons dashicons-info"></span>
            <?php esc_html_e('Add webshop to use this functionnality.', 'affieasy'); ?>
        </p>
    <?php } else { ?>
        <table class="form-table">
            <tbody id="edit-affiliation-link-modal-body">
            <tr id="webshop-row">
                <th scope="row">
                    <label for="webshop-select">
                        <?php esc_html_e('Select webshop', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <select id="webshop-select">
                        <?php foreach ($webshops as $webshop) { ?>
                            <option
                                    value="<?php echo $webshop->getId(); ?>"
                                    data-url="<?php echo $webshop->getUrl(); ?>"
                                    data-parameters="<?php echo implode('|||', $webshop->getParameters()); ?>"
                                    data-link-text-preference="<?php echo $webshop->getLinkTextPreference(); ?>"
                                    data-background-color-preference="<?php echo $webshop->getBackgroundColorPreference(); ?>"
                                    data-text-color-preference="<?php echo $webshop->getTextColorPreference(); ?>">
                                <?php echo $webshop->getName(); ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label>
                        <?php esc_html_e('Link text', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <input
                            type="text"
                            id="link-text-input"
                            maxlength="255">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="link-background-color">
                        <?php esc_html_e('Background color', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <input
                            type="text"
                            id="link-background-color">
                </td>
            </tr>
            <tr id="link-text-color-row">
                <th scope="row">
                    <label for="link-text-color">
                        <?php esc_html_e('Text color', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <input
                            type="text"
                            id="link-text-color">
                </td>
            </tr>
            <?php
            if (!empty($webshops[0]->getParameters())) {
                foreach ($webshops[0]->getParameters() as $parameter) { ?>
                    <tr class="affiliation-parameter-row">
                        <th scope="row">
                            <label>
                                <?php echo $parameter; ?>
                            </label>
                        </th>
                        <td>
                            <input
                                    type="text"
                                    class="affiliation-parameter-input"
                                    maxlength="255"
                                    data-parameter="<?php echo $parameter; ?>"
                                    value="">
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <th scope="row">
                        <label for="affiliation-link-overview">
                            <?php esc_html_e('Link overview', 'affieasy'); ?>
                        </label>
                    </th>

                    <td id="affiliation-link-overview">
                        <?php echo esc_attr($webshops[0]->getUrl()); ?>
                    </td>
                </tr>
            <?php }
            ?>
            </tbody>
        </table>
    <?php } ?>
</div>

<div id="edit-header-options-modal" hidden>
    <input type="hidden" autofocus>
    <div id="edit-header-options-modal-column-options">
        <h3 class="wp-heading-inline"><?php esc_html_e('Column header options', 'affieasy'); ?></h3>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="header-column-background">
                        <?php esc_html_e('Background color', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <input
                            type="text"
                            id="header-column-background"
                            value="<?php echo $hasHeaderOptions ? esc_attr($headerOptions->{'column-background'}) : null; ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="header-column-color">
                        <?php esc_html_e('Text color', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <input
                            type="text"
                            id="header-column-color"
                            value="<?php echo $hasHeaderOptions ? esc_attr($headerOptions->{'column-color'}) : null; ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="header-column-font-weight">
                        <?php esc_html_e('Font weight', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <select id="header-column-font-weight">
                        <?php foreach (AFES_Constants::HEADER_FONT_WEIGHTS as $fontWeight) { ?>
                            <option
                                    value="<?php echo esc_attr($fontWeight); ?>"
                                <?php echo $headerOptions->{'column-font-weight'} == $fontWeight ? 'selected' : ''; ?>>
                                <?php esc_html_e(ucfirst($fontWeight), 'affieasy'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="header-column-font-size">
                        <?php esc_html_e('Font size', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <select id="header-column-font-size">
                        <?php for ($fontSize = 10; $fontSize <= 35; $fontSize++) { ?>
                            <option
                                    value="<?php echo esc_attr($fontSize) . 'px'; ?>"
                                <?php echo $headerOptions->{'column-font-size'} == $fontSize ? 'selected' : ''; ?>>
                                <?php echo esc_attr($fontSize); ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div id="edit-header-options-modal-row-options">
        <h3 class="wp-heading-inline"><?php esc_html_e('Row header options', 'affieasy'); ?></h3>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="header-row-background">
                        <?php esc_html_e('Background color', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <input
                            type="text"
                            id="header-row-background"
                            value="<?php echo $hasHeaderOptions ? esc_attr($headerOptions->{'row-background'}) : null; ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="header-row-color">
                        <?php esc_html_e('Text color', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <input
                            type="text"
                            id="header-row-color"
                            value="<?php echo $hasHeaderOptions ? esc_attr($headerOptions->{'row-color'}) : null; ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="header-row-font-weight">
                        <?php esc_html_e('Font weight', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <select id="header-row-font-weight">
                        <?php foreach (AFES_Constants::HEADER_FONT_WEIGHTS as $fontWeight) { ?>
                            <option
                                    value="<?php echo esc_attr($fontWeight); ?>"
                                <?php echo $headerOptions->{'row-font-weight'} == $fontWeight ? 'selected' : ''; ?>>
                                <?php esc_html_e(ucfirst($fontWeight), 'affieasy'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="header-row-font-size">
                        <?php esc_html_e('Font size', 'affieasy'); ?>
                    </label>
                </th>
                <td>
                    <select id="header-row-font-size">
                        <?php for ($fontSize = 10; $fontSize <= 35; $fontSize++) { ?>
                            <option
                                    value="<?php echo esc_attr($fontSize) . 'px'; ?>"
                                <?php echo $headerOptions->{'row-font-size'} == $fontSize ? 'selected' : ''; ?>>
                                <?php echo esc_attr($fontSize); ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="wrap">

    <div class="header">
        <h1 class="wp-heading-inline"><?php echo empty($tableId) ?
                esc_html__('Create table', 'affieasy') :
                esc_html__('Update table', 'affieasy') . ' ' . esc_html($tableName); ?></h1>

        <a href="admin.php?page=affieasy-table" class="page-title-action">
            <?php esc_html_e('Back to table list', 'affieasy'); ?>
        </a>
    </div>

    <hr class="wp-header-end">
    <?php if ($isFromSaveAction) {
        $hasErrors = count($errors) > 0;
        ?>
        <div
                id="message"
                class="notice notice-<?php echo $hasErrors ? 'error' : 'success' ?> is-dismissible">
            <?php if ($hasErrors) {
                foreach ($errors as $error) { ?>
                    <p><strong><?php echo $error; ?></strong></p>
                <?php }
            } else { ?>
                <p><strong><?php printf(esc_html__('Table %1$s saved', 'affieasy'), $tableName); ?></strong></p>
            <?php } ?>
        </div>
    <?php } ?>

    <form id="form" class="validate" method="post">
        <input type="hidden" id="id" name="id" value="<?php echo $tableId; ?>">
        <input
                type="hidden"
                id="row-id"
                value="<?php echo $isFromSaveActionOrNotNew ? count($table->getContent()) - 1 : 0; ?>">
        <input type="hidden" id="col-id" value="<?php echo count($firstRow); ?>">
        <input type="hidden" id="last-cell-id" value="<?php echo $table->getCellCount(); ?>">
        <input type="hidden" id="initial-header-type" value="<?php echo $table->getHeaderType(); ?>">
        <input type="hidden" id="has-no-webshop" value="<?php echo $hasNoWebShop; ?>">
        <input type="hidden" id="can-use-premium-code" value="<?php echo (int) $canUsePremiumCode; ?>">
        <?php wp_nonce_field('edit-table-nonce', '_wpnonce');?>

        <div class="general-table-options">
            <table class="form-table general-table-options-table" role="presentation">
                <?php if (!empty($tableId)) { ?>
                    <tr class="form-field">
                        <th scope="row" class="general-form-label">
                            <label for="name">
                                <?php printf(esc_html__('Tag', 'affieasy'), $tableName); ?>
                                <span
                                        class="dashicons dashicons-info info"
                                        title="<?php esc_html_e('Put this tag in your page to include the table', 'affieasy'); ?>">
                                </span>
                            </label>
                        </th>
                        <td>
                            <input
                                    type="text"
                                    class="general-input"
                                    maxlength="255"
                                    disabled
                                    value="<?php echo esc_attr($table->getTag()); ?>">
                        </td>
                    </tr>
                <?php } ?>

                <tr class="form-field">
                    <th scope="row" class="general-form-label">
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
                                class="general-input"
                                maxlength="255"
                                value="<?php echo esc_attr($tableName); ?>">
                    </td>
                </tr>

                <tr class="form-field">
                    <th scope="row" class="general-form-label">
                        <label for="header-type">
                            <?php esc_html_e('Header(s)', 'affieasy'); ?>
                        </label>
                    </th>
                    <td>
                        <select id="header-type" name="header-type" class="general-input">
                            <?php foreach (AFES_Constants::HEADERS_TYPES as $key => $value) { ?>
                                <option value="<?php echo $key ?>">
                                    <?php esc_html_e($value, 'affieasy'); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
            </table>

            <table class="form-table general-table-options-table" role="presentation">
                <tr>
                    <th scope="row" class="general-form-label">
                        <label for="max-width">
                            <?php esc_html_e('Max width', 'affieasy'); ?>
                            <span
                                    class="dashicons dashicons-info info"
                                    title="<?php echo esc_html__('Max width in pixels allowed for the table (100% of available space if not filled). ', 'affieasy')
                                        . ($canUsePremiumCode ? '' : esc_html__('Get the premium version to edit this field.', 'affieasy')); ?>">
                                </span>
                        </label>
                    </th>
                    <td>
                        <input
                                type="text"
                                name="max-width"
                                id="max-width"
                                class="general-input"
                                maxlength="5"
                                value="<?php echo esc_attr($table->getMaxWidth()); ?>">
                    </td>
                </tr>

                <tr>
                    <th scope="row" class="general-form-label">
                        <label for="responsive-breakpoint">
                            <?php esc_html_e('Responsive breakpoint', 'affieasy'); ?>
                            <span
                                    class="dashicons dashicons-info info"
                                    title="<?php echo esc_html__('Resolution in pixels below which the table take its responsive form. ', 'affieasy')
                                    . ($canUsePremiumCode ? '' : esc_html__('Get the premium version to edit this field.', 'affieasy')); ?>">
                            </span>
                        </label>
                    </th>
                    <td>
                        <input
                                type="text"
                                name="responsive-breakpoint"
                                id="responsive-breakpoint"
                                class="general-input"
                                maxlength="5"
                                value="<?php echo esc_attr($table->getResponsiveBreakpoint()); ?>">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="background-color">
                            <?php esc_html_e('Background color', 'affieasy'); ?>
                        </label>
                        <?php if (!$canUsePremiumCode) { ?>
                            <span
                                    class="dashicons dashicons-info info"
                                    title="<?php echo esc_html__('Get the premium version to edit this field.', 'affieasy'); ?>">
                            </span>
                        <?php } ?>
                    </th>
                    <td>
                        <input
                                type="text"
                                name="background-color"
                                id="background-color"
                                class="general-input"
                                maxlength="10"
                                value="<?php echo esc_attr($table->getBackgroundColor()); ?>">
                    </td>
                </tr>
            </table>
        </div>

        <div class="action-buttons">
            <button id="add-row-after-last" type="button" class="page-title-action">
                <?php esc_html_e('Add row', 'affieasy'); ?>
            </button>

            <button id="add-column-after-last" type="button" class="page-title-action">
                <?php esc_html_e('Add column', 'affieasy'); ?>
            </button>

            <button id="edit-header-options" type="button" class="page-title-action">
                <?php esc_html_e('Edit header options', 'affieasy'); ?>
            </button>

            <button id="show-tips" type="button" class="page-title-action">
                <?php esc_html_e('Show tips', 'affieasy'); ?>
            </button>
        </div>

        <table id="table-content">
            <thead class="table-content-header">
            <tr id="column-row-buttons">
                <th class="table-col-actions"></th>
                <th id="table-col-actions-cell-0" data-col-id="0"
                    class="table-col-actions-cell table-content-header-row">
                    <div class="table-col-actions-cell-content">
                        <div class="table-col-actions-cell-content-drag"></div>
                        <div class="table-col-actions-cell-content-actions">
                            <span
                                    id="button-col-add-0"
                                    data-col-id="0"
                                    class="dashicons dashicons-plus action-button-add pointer"
                                    title="<?php esc_html_e('Add a column after header', 'affieasy'); ?>">
                                    </span>
                        </div>
                    </div>
                </th>
                <?php for ($i = 0; $i < count($firstRow) - ($headerType === 'ROW_HEADER' ? 1 : 0); $i++) {
                    $colId = $i + 1;
                    ?>
                    <th
                            id="table-col-actions-cell-<?php echo $colId; ?>"
                            data-col-id="<?php echo $colId; ?>"
                            class="sortable-column table-col-actions-cell">
                        <div class="table-col-actions-cell-content">
                            <div class="table-col-actions-cell-content-drag">
                                    <span
                                            class="dashicons dashicons-editor-expand"
                                            title="<?php esc_html_e(
                                                    $canUsePremiumCode ?
                                                        'Keep the mouse pressed to drag and drop the column' :
                                                        'Get the premium version to drag and drop the column',
                                                    'affieasy'); ?>">
                                    </span>
                            </div>
                            <div class="table-col-actions-cell-content-actions">
                                    <span
                                            id="button-col-delete-<?php echo $colId; ?>"
                                            data-col-id="<?php echo $colId; ?>"
                                            class="dashicons dashicons-minus action-button-delete pointer"
                                            title="<?php esc_html_e('Delete column', 'affieasy'); ?>">
                                    </span>
                                <span
                                        id="button-col-add-<?php echo $colId; ?>"
                                        data-col-id="<?php echo $colId; ?>"
                                        class="dashicons dashicons-plus action-button-add pointer"
                                        title="<?php esc_html_e('Add a column after this one', 'affieasy'); ?>">
                                    </span>
                            </div>
                        </div>
                    </th>
                <?php } ?>
            </tr>
            <tr id="row-0" <?php echo $isTableWithColumnHeader ? '' : 'style="display: none"'; ?>>
                <th class="table-row-actions-cell">
                                <span
                                        id="button-row-add-0"
                                        data-row-id="0"
                                        class="dashicons dashicons-plus action-button-add pointer"
                                        title="<?php esc_html_e('Add a row after header', 'affieasy'); ?>">
                                </span>
                </th>
                <th class="table-header-cell table-content-header-row header-without-value" data-col-id="0"></th>
                <?php for ($i = 0; $i < count($firstRow) - ($headerType === 'ROW_HEADER' ? 1 : 0); $i++) { ?>
                    <th class="table-header-cell" data-col-id="<?php echo $i + 1; ?>">
                        <input
                                type="text"
                                class="table-header-cell-content"
                                maxlength="255"
                                value="<?php echo $isTableWithColumnHeader ? $firstRow[$i]->value : ''; ?>">
                    </th>
                <?php } ?>
            </tr>
            </thead>
            <tbody id="table-content-body">
            <?php if ($isFromSaveActionOrNotNew) {
                $cellId = 1;
                for ($i = $isTableWithColumnHeader ? 1 : 0; $i < count($table->getContent()); $i++) {
                    $row = $table->getContent()[$i];

                    $rowId = $isTableWithColumnHeader ? $i : $i + 1; ?>
                    <tr id="row-<?php echo $rowId; ?>">
                        <th class="table-row-actions-cell sortable-row">
                            <span
                                    class="dashicons dashicons-editor-expand drag-row"
                                    title="<?php esc_html_e($canUsePremiumCode ?
                                        'Keep the mouse pressed to drag and drop the row' :
                                        'Get the premium version to drag and drop the row', 'affieasy'); ?>">
                            </span>
                            <span
                                    id="button-row-delete-<?php echo $rowId; ?>"
                                    data-row-id="<?php echo $rowId; ?>"
                                    class="dashicons dashicons-minus action-button-delete pointer"
                                    title="<?php esc_html_e('Delete row', 'affieasy'); ?>">
                                </span>
                            <span
                                    id="button-row-add-<?php echo $rowId; ?>"
                                    data-row-id="<?php echo $rowId; ?>"
                                    class="dashicons dashicons-plus action-button-add pointer"
                                    title="<?php esc_html_e('Add a row after this one', 'affieasy'); ?>">
                            </span>
                        </th>
                        <td
                                id="cell-0"
                                class="table-content-cell-html table-content-header-row"
                                data-col-id="0"
                                data-cell-type="<?php echo esc_attr($row[1]->type); ?>">
                            <input
                                    type="text"
                                    maxLength="255"
                                    class="table-header-row-cell-content"
                                    value="<?php echo $isTableWithRowHeader ? esc_attr($row[0]->value) : ''; ?>">
                        </td>
                        <?php for ($j = $isTableWithRowHeader ? 1 : 0; $j < count($row); $j++) {
                            $cellType = $row[$j]->type;
                            $cellValue = $cellType == AFES_Constants::AFFILIATION ?
                                $row[$j]->value :
                                str_replace('&amp;NewLine;', '&NewLine;', $row[$j]->value);

                            $colId = $isTableWithRowHeader ? $j : $j + 1;
                            if ($cellType == AFES_Constants::HTML) { ?>
                                <td
                                        id="cell-<?php echo $cellId; ?>"
                                        class="table-content-cell-html"
                                        data-col-id="<?php echo $colId; ?>"
                                        data-cell-type="<?php echo $cellType; ?>">
                                    <textarea
                                            maxLength="2048"
                                            class="table-content-cell-html-content"><?php echo $cellValue; ?></textarea>
                                </td>
                            <?php } else if ($cellType == AFES_Constants::IMAGE) { ?>
                                <td
                                        id="cell-<?php echo $cellId; ?>"
                                        class="table-content-cell-image"
                                        data-col-id="<?php echo $colId; ?>"
                                        data-cell-type="<?php echo $cellType; ?>">
                                    <input
                                            id="cell-content-<?php echo $cellId; ?>"
                                            name="cell-content-<?php echo $cellId; ?>"
                                            type="hidden"
                                            autocomplete="off"
                                            value="<?php echo $cellValue; ?>">
                                    <span
                                            id="select-image-button-<?php echo $cellId; ?>"
                                            class="dashicons dashicons-edit select-image-button action-button-add pointer"
                                            title="<?php esc_html_e('Select image', 'affieasy'); ?>"
                                            data-cell-id="<?php echo $cellId; ?>">
                                        </span>
                                    <?php if (!empty($cellValue)) { ?>
                                        <span
                                                id="remove-image-button-<?php echo $cellId; ?>"
                                                class="dashicons dashicons-minus remove-image-button action-button-delete pointer"
                                                title="<?php esc_html_e('Remove image', 'affieasy'); ?>"
                                                data-cell-id="<?php echo $cellId; ?>">
                                        </span>
                                    <?php } ?>
                                    <div
                                            id="table-content-cell-image-overview-<?php echo $cellId; ?>"
                                            class="table-content-cell-image-overview">
                                        <?php echo empty($cellValue) ?
                                            '' :
                                            substr($cellValue, 0, -1) . " class='table-content-cell-image-overview-content'>"; ?>
                                    </div>
                                </td>
                            <?php } else if ($cellType == AFES_Constants::AFFILIATION) {
                                $affiliateLinks = json_decode(str_replace("&quot;", '"', $cellValue));
                                ?>
                                <td
                                        id="cell-<?php echo $cellId; ?>"
                                        class="table-content-cell-affiliation"
                                        data-col-id="<?php echo $colId; ?>"
                                        data-cell-type="<?php echo $cellType; ?>">
                                    <input
                                            id="cell-content-<?php echo $cellId; ?>"
                                            name="cell-content-<?php echo $cellId; ?>"
                                            type="hidden"
                                            autocomplete="off"
                                            value="<?php echo $cellValue; ?>">
                                    <span
                                            class="dashicons dashicons-plus add-affiliation-link-button action-button-add pointer"
                                            title="<?php esc_html_e('Add affiliate link', 'affieasy'); ?>"
                                            data-cell-id="<?php echo $cellId; ?>">
                                        </span>
                                    <div id="cell-content-link-list-<?php echo $cellId; ?>">
                                        <?php foreach ($affiliateLinks as $affiliateLink) { ?>
                                            <button
                                                    type="button"
                                                    class="affiliation-table-affiliate-link cell-content-link-list-button"
                                                <?php echo AFES_GenerationUtils::get_affiliate_link_style($affiliateLink); ?>
                                                    title="<?php esc_html_e('Edit affiliate link', 'affieasy'); ?>"
                                                    data-cell-id="<?php echo $cellId; ?>"
                                                    data-id="<?php echo $affiliateLink->id; ?>">
                                                <span class="dashicons dashicons-cart cell-content-link-list-icon"></span>
                                                <span><?php echo $affiliateLink->linkText; ?></span>
                                            </button>
                                        <?php } ?>
                                    </div>
                                </td>
                            <?php }

                            $cellId++;
                        } ?>
                    </tr>
                <?php }
            } ?>
            </tbody>
        </table>

        <div class="informations">
            <i>
                <span class="dashicons dashicons-info"></span>
                <span class="informations-text">
                    <?php esc_html_e(
                            'For better readability, background color modifications are not visible into the editor.',
                            'affieasy'); ?>

                    <?php esc_html_e(
                        'Rendering can be slightly different depending on the theme applied.',
                        'affieasy'); ?>
                </span>
            </i>
        </div>

        <div id="table-content-values">
        </div>
        <input
                type="text"
                id="header-options"
                name="header-options"
                value='<?php echo json_encode($headerOptions); ?>'
                hidden>
    </form>

    <button
            type="submit"
            form="form"
            name="submit"
            id="submit"
            class="button button-primary edit-button-bottom"
            value="save-action">
        <?php esc_html_e('Save table', 'affieasy'); ?>
    </button>

    <div id="popovers">
        <div id="add-row-popover">
            <h3 class="popover-header"><?php esc_html_e('Row type', 'affieasy'); ?></h3>
            <div class="add-row-popover-content">
                <button type="button" id="add-html-row" class="button-primary add-row-popover-button">
                    <?php esc_html_e('Text / Html', 'affieasy'); ?>
                </button>
                <button
                        type="button"
                        id="add-image-row"
                        class="button-primary add-row-popover-button">
                    <?php esc_html_e('Images', 'affieasy'); ?>
                </button>
                <button
                        type="button"
                        id="add-affiliation-row"
                        class="button-primary add-row-popover-button <?php echo $hasNoWebShop ? 'disabled' : '' ?>"
                    <?php echo $hasNoWebShop ? 'title="' . esc_html__("Add webshop to use this functionnality.", "affieasy") . '" disabled' : ''; ?>>
                    <?php esc_html_e('Affiliate links', 'affieasy'); ?>
                    <?php if ($hasNoWebShop) { ?>
                        <span class="dashicons dashicons-info dashicons-button-disabled"></span>
                    <?php } ?>
                </button>
            </div>
        </div>
        <div id="show-tips-popover">
            <h3 class="popover-header"><?php esc_html_e('Tips', 'affieasy'); ?></h3>
            <p>
                <?php esc_html_e('You can use html in each cells: for example if you want to line break, use &lt;br&gt;, &lt;ul&gt;...&lt;/ul&gt; for a list...', 'affieasy'); ?>
            </p>
            <p>
                <?php esc_html_e('The shortcodes mentioned below allow you to easily add icons to your tables (ask us via support if you want more).', 'affieasy'); ?>
            </p>

            <div class="show-tips-popover-icon-list">
                <?php foreach (AFES_Constants::AVAILABLE_ICONS as $key => $value) { ?>
                    <div><?php echo $key; ?></div>
                    <div><span class="dashicons dashicons-<?php echo $value ?>"></span></div>
                <?php } ?>
            </ul>
        </div>
    </div>
</div>