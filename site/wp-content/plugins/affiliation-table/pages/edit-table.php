<?php
wp_enqueue_style(
    'edit-table-style',
    plugins_url('/affiliation-table/css/edit-table.css'),
    array(),
    time());

wp_enqueue_style(
    'popover-modal-style',
    plugins_url('/affiliation-table/libs/pop-modal/pop-modal.min.css'),
    array(),
    time());

wp_register_script('pop-modal', plugins_url('/affiliation-table/libs/pop-modal/pop-modal.min.js'), array('jquery'));
wp_register_script('table-dragger', plugins_url('/affiliation-table/libs/table-dragger/table-dragger.min.js'));

wp_enqueue_script(
    'edit-table-script',
    plugins_url('/affiliation-table/js/edit-table.js'),
    array('jquery', 'pop-modal', 'table-dragger'),
    time()
);

$table = new Table($_POST['id'], $_POST['name'], $_POST['with-header'], $_POST['content']);
$errors = array();
$dbManager = new DbManager();

$isFromSaveAction = $_POST['submit'] == 'save-action';
if ($isFromSaveAction) {
    if (empty($table->getName())) {
        array_push($errors, 'Name must not be empty');
    }

    $isTableWithHeader = $table->isWithHeader() == 1;
    $tableContentSize = count($table->getContent());
    if ($isTableWithHeader && $tableContentSize < 2 || !$isTableWithHeader && $tableContentSize < 1) {
        array_push($errors, 'Table must contains at least one row');
    }

    if (count($errors) == 0) {
        $table = $dbManager->edit_table($table);
    } else {
        $table->setContent(array_map(function ($row) {
            return array_map(function ($cell) {
                $cellContent = json_decode(str_replace("\\", "", str_replace('\\\\\\"', "&quot;", $cell)));

                return (object)[
                    'type' => 'html',
                    'value' => $cellContent->value,
                ];
            }, $row);
        }, $table->getContent()));
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
$isTableWithHeader = $table->isWithHeader() == 1;

$isFromSaveActionOrNotNew = $isFromSaveAction || !empty($table->getId());
?>

<div class="wrap">
    <h1><?php echo empty($tableId) ? 'Create table' : 'Update table ' . $tableId; ?></h1>

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
    <? } ?>

    <nav class="nav-tab-wrapper wp-clearfix" aria-label="Menu secondaire">
        <span id="edition-nav" class="nav-tab nav-tab-active" aria-current="page">Edition</span>
        <span id="overview-nav" class="nav-tab" aria-current="page">Overview</span>
    </nav>

    <div id="edition-panel">
        <form id="form" class="validate" method="post">
            <input type="hidden" id="id" name="id" value="<?php echo $tableId; ?>">
            <input
                    type="hidden"
                    id="row-id"
                    value="<?php echo $isFromSaveActionOrNotNew ? count($table->getContent()) - 1 : 0 ?>">
            <input type="hidden" id="col-id" value="<?php echo count($firstRow); ?>">

            <table class="form-table" role="presentation">
                <?php if (!empty($tableId)) { ?>
                    <tr class="form-field">
                        <th scope="row">
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
                                    class="name-input"
                                    maxlength="255"
                                    disabled
                                    value="<?php echo $table->getTag(); ?>">
                        </td>
                    </tr>
                <?php } ?>

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
                                class="name-input"
                                maxlength="255"
                                value="<?php echo $tableName; ?>">
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="with-header">
                            Table with header
                        </label>
                    </th>
                    <td>
                        <input
                                type="checkbox"
                                id="with-header"
                                name="with-header"
                            <?php echo $isTableWithHeader || !$isFromSaveActionOrNotNew ? 'checked' : '' ?>>
                    </td>
                </tr>
            </table>

            <div class="action-buttons">
                <button id="add-row-after-last" type="button" class="page-title-action">
                    Add row
                </button>

                <button id="add-column-after-last" type="button" class="page-title-action">
                    Add column
                </button>
            </div>

            <table id="table-content">
                <thead class="table-content-header">
                <tr id="column-row-buttons">
                    <th data-col-id="0"></th>
                    <?php for ($i = 1; $i <= count($firstRow); $i++) { ?>
                        <th
                                id="table-col-actions-cell-<?php echo $i; ?>"
                                data-col-id="<?php echo $i; ?>"
                                class="sortable-column">
                            <div class="table-col-actions-cell-content">
                                <div class="table-col-actions-cell-content-drag">
                                    <span
                                            class="dashicons dashicons-editor-expand"
                                            title="Keep the mouse pressed to drag and drop the column">
                                    </span>
                                </div>
                                <div class="table-col-actions-cell-content-actions">
                                    <span
                                            id="button-col-delete-<?php echo $i; ?>"
                                            data-col-id="<?php echo $i; ?>"
                                            class="dashicons dashicons-minus action-button action-button-delete"
                                            title="Delete column">
                                    </span>
                                    <span
                                            id="button-col-add-<?php echo $i; ?>"
                                            data-col-id="<?php echo $i; ?>"
                                            class="dashicons dashicons-plus action-button action-button-add"
                                            title="Add a column after this one">
                                    </span>
                                </div>
                            </div>
                        </th>
                    <?php } ?>
                </tr>
                <tr id="row-0" <?php echo $isTableWithHeader ? '' : 'style="display: none"'; ?>>
                    <th class="table-row-actions-cell" data-col-id="0">
                                <span
                                        id="button-row-add-0"
                                        data-row-id="0"
                                        class="dashicons dashicons-plus action-button action-button-add"
                                        title="Add a row after header">
                                </span>
                    </th>
                    <?php for ($i = 1; $i <= count($firstRow); $i++) { ?>
                        <th class="table-header-cell" data-col-id="<?php echo $i; ?>">
                            <input
                                    type="text"
                                    class="table-header-cell-content"
                                    maxlength="255"
                                    value="<?php echo $isTableWithHeader ? $firstRow[$i - 1]->value : ''; ?>">
                        </th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody id="table-content-body">
                <?php if ($isFromSaveActionOrNotNew) {
                    for ($i = $isTableWithHeader ? 1 : 0; $i < count($table->getContent()); $i++) {
                        $row = $table->getContent()[$i];

                        $rowId = $isTableWithHeader ? $i : $i + 1; ?>
                        <tr id="row-<?php echo $rowId; ?>">
                            <td class="table-row-actions-cell sortable-row">
                                <span
                                        class="dashicons dashicons-editor-expand action-button drag-row"
                                        title="Keep the mouse pressed to drag and drop the row">
                                </span>
                                <span
                                        id="button-row-delete-<?php echo $rowId; ?>"
                                        data-row-id="<?php echo $rowId; ?>"
                                        class="dashicons dashicons-minus action-button action-button-delete"
                                        title="Delete row">
                                </span>
                                <span
                                        id="button-row-add-<?php echo $rowId; ?>"
                                        data-row-id="<?php echo $rowId; ?>"
                                        class="dashicons dashicons-plus action-button action-button-add"
                                        title="Add a row after this one">
                                </span>
                            </td>
                            <?php for ($j = 0; $j < count($row); $j++) { ?>
                                <td class="table-content-cell" data-col-id="<?php echo $j + 1; ?>">
                                    <textarea maxLength="2048"
                                              class="table-content-cell-content"><?php echo $row[$j]->value; ?></textarea>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php }
                } ?>
                </tbody>
            </table>

            <div id="table-content-values">

            </div>
        </form>
    </div>

    <div id="overview-panel">
    </div>

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
                <button type="button" id="add-html-row" class="page-title-action">
                    Text / Html
                </button>
                <button type="button" class="page-title-action">
                    Images
                </button>
                <button type="button" class="page-title-action">
                    Affiliate links
                </button>
            </div>
        </div>
    </div>
</div>