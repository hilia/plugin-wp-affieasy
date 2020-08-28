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
    array('jquery', 'pop-modal', 'jquery-ui-sortable', 'table-dragger'),
    time()
);

$defaultTableColumnNumber = 4;

?>

<div class="wrap">
    <h1>Create Table</h1>

    <nav class="nav-tab-wrapper wp-clearfix" aria-label="Menu secondaire">
        <span id="edition-nav" class="nav-tab nav-tab-active" aria-current="page">Edition</span>
        <span id="overview-nav" class="nav-tab" aria-current="page">Overview</span>
    </nav>

    <div id="edition-panel">
        <form class="validate" method="post">
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
                                class="name-input"
                                maxlength="255"
                                value="">
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
                                checked>
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
                    <th data-col-number="0"></th>
                    <?php for ($i = 1; $i <= $defaultTableColumnNumber; $i++) { ?>
                        <th
                                id="table-col-actions-cell-<?php echo $i; ?>"
                                data-col-number="<?php echo $i; ?>"
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
                                            data-col-number="<?php echo $i; ?>"
                                            class="dashicons dashicons-minus action-button action-button-delete"
                                            title="Delete column">
                                    </span>
                                    <span
                                            id="button-col-add-<?php echo $i; ?>"
                                            data-col-number="<?php echo $i; ?>"
                                            class="dashicons dashicons-plus action-button action-button-add"
                                            title="Add a column after this one">
                                    </span>
                                </div>
                            </div>
                        </th>
                    <?php } ?>
                </tr>
                <tr id="row-0">
                    <th class="table-row-actions-cell" data-col-number="0">
                                <span
                                        id="button-row-0"
                                        class="dashicons dashicons-plus action-button action-button-add"
                                        title="Add a row after header">
                                </span>
                    </th>
                    <?php for ($i = 1; $i <= $defaultTableColumnNumber; $i++) { ?>
                        <th class="table-header-cell" data-col-number="<?php echo $i; ?>">
                            <input type="text" class="table-header-cell-content" maxlength="255">
                        </th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody class="table-content-body">
                </tbody>
            </table>
        </form>
    </div>

    <div id="overview-panel">
    </div>

    <button
            type="submit"
            name="submit"
            id="submit"
            class="button button-primary edit-button-bottom"
            value="edit-table">
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