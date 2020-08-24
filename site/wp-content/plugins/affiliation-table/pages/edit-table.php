<?php
wp_enqueue_style(
    'edit-table-style',
    plugins_url('/affiliation-table/css/edit-table.css'),
    array(),
    time());

wp_enqueue_script(
    'edit-table-script',
    plugins_url('/affiliation-table/js/edit-table.js'),
    array('jquery', 'jquery-ui-dialog'),
    time()
);
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
            </div>

            <table class="table-content">
                <thead class="table-content-header">
                <tr id="row-0">
                    <td class="table-cell-actions">
                            <span
                                    id="add-row-after-header"
                                    class="dashicons dashicons-plus action-button action-button-add"
                                    title="Add row">
                            </span>
                    </td>
                    <td class="table-header-cell">
                        <input type="text" class="table-header-cell-content" maxlength="255">
                    </td>
                    <td class="table-header-cell">
                        <input type="text" class="table-header-cell-content" maxlength="255">
                    </td>
                    <td class="table-header-cell">
                        <input type="text" class="table-header-cell-content" maxlength="255">
                    </td>
                    <td class="table-header-cell">
                        <input type="text" class="table-header-cell-content" maxlength="255">
                    </td>
                </tr>
                </thead>
                <tbody class="table-content-body">
                </tbody>
            </table>
        </form>
    </div>

    <div id="overview-panel" style="display: none;">
    </div>

    <button
            type="submit"
            name="submit"
            id="submit"
            class="button button-primary edit-button-bottom"
            value="edit-table">
        Save table
    </button>
</div>