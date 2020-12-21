<?php
wp_enqueue_style(
    'edit-table-style',
    plugins_url('/affiliation-table/css/edit-table.css'),
    array(),
    time());

wp_enqueue_style(
    'color-picker-style',
    plugins_url('/affiliation-table/libs/color-picker/color-picker.css'),
    array(),
    time());

wp_enqueue_style(
    'popover-modal-style',
    plugins_url('/affiliation-table/libs/pop-modal/pop-modal.min.css'),
    array(),
    time());

wp_enqueue_style('wp-jquery-ui-dialog');

wp_register_script('color-picker', plugins_url('/affiliation-table/libs/color-picker/color-picker.min.js'));
wp_register_script('pop-modal', plugins_url('/affiliation-table/libs/pop-modal/pop-modal.min.js'), array('jquery'));
wp_register_script('table-dragger', plugins_url('/affiliation-table/libs/table-dragger/table-dragger.min.js'));

wp_enqueue_script(
    'edit-table-script',
    plugins_url('/affiliation-table/js/edit-table.js'),
    array('jquery', 'color-picker', 'pop-modal', 'table-dragger', 'jquery-ui-dialog'),
    time()
);

wp_enqueue_media();

$table = new Table(
    $_POST['id'],
    $_POST['name'],
    $_POST['header-type'],
    $_POST['header-options'],
    $_POST['content'],
    $_POST['responsive-breakpoint'],
    $_POST['max-width'],
    $_POST['background-color']
);

$errors = array();
$dbManager = new DbManager();
$webshops = $dbManager->get_webshop_list();
$hasNoWebShop = empty($webshops);

$isFromSaveAction = $_POST['submit'] == 'save-action';
if ($isFromSaveAction) {
    if (empty($table->getName())) {
        array_push($errors, 'Name must not be empty');
    }

    $isNullTableContent = $table->getContent() == null;
    $isTableWithColumnHeader = in_array($table->getHeaderType(), array('COLUMN_HEADER', 'BOTH'));
    $tableContentSize = $isNullTableContent ? 0 : count($table->getContent());
    if ($isTableWithColumnHeader && $tableContentSize < 2 || !$isTableWithColumnHeader && $tableContentSize < 1) {
        array_push($errors, 'Table must contains at least one row');
    }

    $responsiveBreakpoint = $table->getResponsiveBreakpoint();
    if ($responsiveBreakpoint !== '' && (!is_numeric($responsiveBreakpoint) || $responsiveBreakpoint < 0)) {
        array_push($errors, 'Responsive breakpoint must be a positive number');
    }

    $maxWidth = $table->getMaxWidth();
    if ($maxWidth !== '' && (!is_numeric($maxWidth) || $maxWidth < 0)) {
        array_push($errors, 'Max width must be a positive number');
    }

    if (count($errors) == 0) {
        $table = $dbManager->edit_table($table, false);
    } else {
        if ($isNullTableContent) {
            $table->initDefaultContent();
        } else {
            $table->setContent(array_map(function ($row) {
                return array_map(function ($cell) {
                    $cellContent = json_decode(
                        str_replace("\\", "",
                            str_replace('\\\\\\"', "&quot;",
                                str_replace('\\n', '&NewLine;', $cell))));

                    return (object)[
                        'type' => $cellContent->type,
                        'value' => $cellContent->value,
                    ];
                }, $row);
            }, $table->getContent()));

            $table->setHeaderOptions(json_decode(str_replace('\\', '', $table->getHeaderOptions())));
        }
    }
} else {
    $id = $_GET['id'];
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
            Add webshops to use this functionnality.
        </p>
    <?php } else { ?>
        <table class="form-table">
            <tbody id="edit-affiliation-link-modal-body">
            <tr id="webshop-row">
                <th scope="row">
                    <label for="webshop-select">
                        Select webshop
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
                        Link text
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
                        Background color
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
                        Text color
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
                            Link overview
                        </label>
                    </th>

                    <td id="affiliation-link-overview">
                        <?php echo $webshops[0]->getUrl(); ?>
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
        <h3 class="wp-heading-inline">Column header options</h3>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="header-column-background">
                        Background color
                    </label>
                </th>
                <td>
                    <input
                            type="text"
                            id="header-column-background"
                            value="<?php echo $hasHeaderOptions ? $headerOptions->{'column-background'} : null; ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="header-column-color">
                        Text color
                    </label>
                </th>
                <td>
                    <input
                            type="text"
                            id="header-column-color"
                            value="<?php echo $hasHeaderOptions ? $headerOptions->{'column-color'} : null; ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="header-column-font-weight">
                        Font weight
                    </label>
                </th>
                <td>
                    <select id="header-column-font-weight">
                        <?php foreach (Constants::HEADER_FONT_WEIGHTS as $fontWeight) { ?>
                            <option
                                    value="<?php echo $fontWeight; ?>"
                                <?php echo $headerOptions->{'column-font-weight'} == $fontWeight ? 'selected' : ''; ?>>
                                <?php echo ucfirst($fontWeight); ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="header-column-font-size">
                        Font size
                    </label>
                </th>
                <td>
                    <select id="header-column-font-size">
                        <?php for ($fontSize = 10; $fontSize <= 35; $fontSize++) { ?>
                            <option
                                    value="<?php echo $fontSize . 'px'; ?>"
                                <?php echo $headerOptions->{'column-font-size'} == $fontSize ? 'selected' : ''; ?>>
                                <?php echo $fontSize; ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div id="edit-header-options-modal-row-options">
        <h3 class="wp-heading-inline">Row header options</h3>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="header-row-background">
                        Background color
                    </label>
                </th>
                <td>
                    <input
                            type="text"
                            id="header-row-background"
                            value="<?php echo $hasHeaderOptions ? $headerOptions->{'row-background'} : null; ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="header-row-color">
                        Text color
                    </label>
                </th>
                <td>
                    <input
                            type="text"
                            id="header-row-color"
                            value="<?php echo $hasHeaderOptions ? $headerOptions->{'row-color'} : null; ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="header-row-font-weight">
                        Font weight
                    </label>
                </th>
                <td>
                    <select id="header-row-font-weight">
                        <?php foreach (Constants::HEADER_FONT_WEIGHTS as $fontWeight) { ?>
                            <option
                                    value="<?php echo $fontWeight; ?>"
                                <?php echo $headerOptions->{'row-font-weight'} == $fontWeight ? 'selected' : ''; ?>>
                                <?php echo ucfirst($fontWeight); ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="header-row-font-size">
                        Font size
                    </label>
                </th>
                <td>
                    <select id="header-row-font-size">
                        <?php for ($fontSize = 10; $fontSize <= 35; $fontSize++) { ?>
                            <option
                                    value="<?php echo $fontSize . 'px'; ?>"
                                <?php echo $headerOptions->{'row-font-size'} == $fontSize ? 'selected' : ''; ?>>
                                <?php echo $fontSize; ?>
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
        <h1 class="wp-heading-inline"><?php echo empty($tableId) ? 'Create table' : 'Update table ' . $tableName; ?></h1>

        <a href="admin.php?page=affiliation-table-table" class="page-title-action">
            Back to table list
        </a>
    </div>

    <hr class="wp-header-end">

    <?php if ($isFromSaveAction) {
        $hasErrors = count($errors) > 0;
        ?>
        <div
                id="setting-error-settings_updated"
                class="notice notice-<?php echo $hasErrors ? 'error' : 'success' ?> settings-error is-dismissible">
            <?php if ($hasErrors) {
                foreach ($errors as $error) { ?>
                    <p><strong><?php echo $error; ?></strong></p>
                <?php }
            } else { ?>
                <p><strong>Table <?php echo $tableName; ?> saved</strong></p>
            <?php } ?>
            <button type="button" class="notice-dismiss"></button>
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

        <div class="general-table-options">
            <table class="form-table" role="presentation">
                <?php if (!empty($tableId)) { ?>
                    <tr class="form-field">
                        <th scope="row" class="general-form-label">
                            <label for="name">
                                Tag
                                <span
                                        class="dashicons dashicons-info"
                                        title="Put this tag in your page to include the table">
                                </span>
                            </label>
                        </th>
                        <td>
                            <input
                                    type="text"
                                    class="general-input"
                                    maxlength="255"
                                    disabled
                                    value="<?php echo $table->getTag(); ?>">
                        </td>
                    </tr>
                <?php } ?>

                <tr class="form-field">
                    <th scope="row" class="general-form-label">
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
                                class="general-input"
                                maxlength="255"
                                value="<?php echo $tableName; ?>">
                    </td>
                </tr>

                <tr class="form-field">
                    <th scope="row" class="general-form-label">
                        <label for="header-type">
                            Header(s)
                        </label>
                    </th>
                    <td>
                        <select id="header-type" name="header-type" class="general-input">
                            <?php foreach (Constants::HEADERS_TYPES as $key => $value) { ?>
                                <option value="<?php echo $key ?>">
                                    <?php echo $value ?>
                                </option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
            </table>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row" class="general-form-label">
                        <label for="name">
                            Max width
                            <span
                                    class="dashicons dashicons-info"
                                    title="Max width in pixels allowed for the table (100% of available space if not filled)">
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
                                value="<?php echo $table->getMaxWidth(); ?>">
                    </td>
                </tr>

                <tr>
                    <th scope="row" class="general-form-label">
                        <label for="name">
                            Responsive breakpoint
                            <span
                                    class="dashicons dashicons-info"
                                    title="Resolution in pixels below wich the table take its responsive form">
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
                                value="<?php echo $table->getResponsiveBreakpoint(); ?>">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="name">
                            Background color
                        </label>
                    </th>
                    <td>
                        <input
                                type="text"
                                name="background-color"
                                id="background-color"
                                class="general-input"
                                maxlength="10"
                                value="<?php echo $table->getBackgroundColor(); ?>">
                    </td>
                </tr>
            </table>
        </div>

        <div class="action-buttons">
            <button id="add-row-after-last" type="button" class="page-title-action">
                Add row
            </button>

            <button id="add-column-after-last" type="button" class="page-title-action">
                Add column
            </button>

            <button id="edit-header-options" type="button" class="page-title-action">
                Edit header options
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
                                    title="Add a column after header">
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
                                            title="Keep the mouse pressed to drag and drop the column">
                                    </span>
                            </div>
                            <div class="table-col-actions-cell-content-actions">
                                    <span
                                            id="button-col-delete-<?php echo $colId; ?>"
                                            data-col-id="<?php echo $colId; ?>"
                                            class="dashicons dashicons-minus action-button-delete pointer"
                                            title="Delete column">
                                    </span>
                                <span
                                        id="button-col-add-<?php echo $colId; ?>"
                                        data-col-id="<?php echo $colId; ?>"
                                        class="dashicons dashicons-plus action-button-add pointer"
                                        title="Add a column after this one">
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
                                        title="Add a row after header">
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
                                    title="Keep the mouse pressed to drag and drop the row">
                            </span>
                            <span
                                    id="button-row-delete-<?php echo $rowId; ?>"
                                    data-row-id="<?php echo $rowId; ?>"
                                    class="dashicons dashicons-minus action-button-delete pointer"
                                    title="Delete row">
                                </span>
                            <span
                                    id="button-row-add-<?php echo $rowId; ?>"
                                    data-row-id="<?php echo $rowId; ?>"
                                    class="dashicons dashicons-plus action-button-add pointer"
                                    title="Add a row after this one">
                            </span>
                        </th>
                        <td
                                id="cell-0"
                                class="table-content-cell-html table-content-header-row"
                                data-col-id="0"
                                data-cell-type="<?php echo $row[1]->type; ?>">
                            <input
                                    type="text"
                                    maxLength="255"
                                    class="table-header-row-cell-content"
                                    value="<?php echo $isTableWithRowHeader ? $row[0]->value : ''; ?>">
                        </td>
                        <?php for ($j = $isTableWithRowHeader ? 1 : 0; $j < count($row); $j++) {
                            $cellType = $row[$j]->type;
                            $cellValue = $row[$j]->value;

                            $colId = $isTableWithRowHeader ? $j : $j + 1;
                            if ($cellType == Constants::HTML) { ?>
                                <td
                                        id="cell-<?php echo $cellId; ?>"
                                        class="table-content-cell-html"
                                        data-col-id="<?php echo $colId; ?>"
                                        data-cell-type="<?php echo $cellType; ?>">
                                    <textarea
                                            maxLength="2048"
                                            class="table-content-cell-html-content"><?php echo $cellValue; ?></textarea>
                                </td>
                            <?php } else if ($cellType == Constants::IMAGE) { ?>
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
                                            title="Select image"
                                            data-cell-id="<?php echo $cellId; ?>">
                                        </span>
                                    <?php if (!empty($cellValue)) { ?>
                                        <span
                                                id="remove-image-button-<?php echo $cellId; ?>"
                                                class="dashicons dashicons-minus remove-image-button action-button-delete pointer"
                                                title="Remove image"
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
                            <?php } else if ($cellType == Constants::AFFILIATION) {
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
                                            title="Add affiliate link"
                                            data-cell-id="<?php echo $cellId; ?>">
                                        </span>
                                    <div id="cell-content-link-list-<?php echo $cellId; ?>">
                                        <?php foreach ($affiliateLinks as $affiliateLink) { ?>
                                            <button
                                                    type="button"
                                                    class="affiliation-table-affiliate-link cell-content-link-list-button"
                                                <?php echo GenerationUtils::get_affiliate_link_style($affiliateLink); ?>
                                                    title="Edit affiliate link"
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
                    For better clarity, background color modifications are not visible into the editor.
                    Rendering can be slightly different depending on the theme applied.
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
        Save table
    </button>

    <div id="popovers">
        <div id="add-row-popover">
            <h3 class="add-row-popover-header">Row type</h3>
            <div class="add-row-popover-content">
                <button type="button" id="add-html-row" class="button-primary add-row-popover-button">
                    Text / Html
                </button>
                <button
                        type="button"
                        id="add-image-row"
                        class="button-primary add-row-popover-button"
                        title="Not yet implemented">
                    Images
                </button>
                <button
                        type="button"
                        id="add-affiliation-row"
                        class="button-primary add-row-popover-button <?php echo $hasNoWebShop ? 'disabled' : '' ?>"
                    <?php echo $hasNoWebShop ? 'title="Add webshops to use this functionnality" disabled' : '' ?>>
                    Affiliate links
                    <?php if ($hasNoWebShop) { ?>
                        <span class="dashicons dashicons-info dashicons-button-disabled"></span>
                    <?php } ?>
                </button>
            </div>
        </div>
    </div>
</div>