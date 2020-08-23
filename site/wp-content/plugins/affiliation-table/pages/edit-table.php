<?php
wp_enqueue_style(
    'edit-table-style',
    plugins_url('/affiliation-table/css/edit-table.css'),
    array(),
    time());

wp_enqueue_script(
    'edit-table-script',
    plugins_url('/affiliation-table/js/edit-table.js'),
    array('jquery'),
    time()
);

?>

<div class="wrap">
    <h1>Create Table</h1>

    <nav class="nav-tab-wrapper wp-clearfix" aria-label="Menu secondaire">
        <span id="edition-nav" class="nav-tab nav-tab-active" aria-current="page"
              onclick="updateActivePanel('nav-edition')">Edition</span>
        <span id="overview-nav" class="nav-tab" aria-current="page"
              onclick="updateActivePanel('nav-overview')">Overview</span>
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
                        <input type="checkbox" id="with-header" name="with-header" checked
                               onchange="toggleWithHeader()">
                    </td>
                </tr>
            </table>

            <div class="table-content">
                <input type="text" class="header-cell" maxlength="255">
                <input type="text" class="header-cell" maxlength="255">
                <input type="text" class="header-cell" maxlength="255">
                <input type="text" class="header-cell" maxlength="255">
            </div>
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