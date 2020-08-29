jQuery(($) => {
    let currentRowId = null;

    let columnDragger = null;
    let rowDragger = null;

    displayOrHideHeaderRow();
    initDragAndDropColumn();
    initDragAndDropRow();

    // Switch to edition panel
    $('#edition-nav').on('click', () => {
        $('#edition-nav').addClass('nav-tab-active');
        $('#overview-nav').removeClass('nav-tab-active');

        $('#edition-panel').css('display', 'block');
        $('#overview-panel').css('display', 'none');
    });

    // Switch to overview panel
    $('#overview-nav').on('click', () => {
        $('#edition-nav').removeClass('nav-tab-active');
        $('#overview-nav').addClass('nav-tab-active');

        $('#edition-panel').css('display', 'none');
        $('#overview-panel').css('display', 'block');
    });

    // Add hide or display header row event
    $('#with-header').on('change', () => {
        displayOrHideHeaderRow();
    });

    // Open the creation row popover to add a row after the last
    $('#add-row-after-last').on('click', () => {
        currentRowId = null;

        $('#add-row-after-last').popModal({
            html: $('#add-row-popover'),
            placement: 'rightTop'
        });
    });

    // Add a new column after the last
    $('#add-column-after-last').on('click', () => {
        addColumnAfter();
    });

    // Add delete col events
    $("[id*=button-col-delete-]").each((index, element) => {
        const jqueryElement = $(element);
        jqueryElement.on('click', null, {colId: jqueryElement.data('col-id')}, deleteColumn);
    });

    // Add add col events
    $("[id*=button-col-add-]").each((index, element) => {
        const jqueryElement = $(element);
        jqueryElement.on('click', null, {colId: jqueryElement.data('col-id')}, addColumnAfter);
    });

    // Add delete row events
    $("[id*=button-row-delete-]").each((index, element) => {
        const jqueryElement = $(element);
        jqueryElement.on('click', null, {rowId: jqueryElement.data('row-id')}, deleteRow);
    });

    // Add add row events
    $("[id*=button-row-add-]").each((index, element) => {
        const jqueryElement = $(element);
        jqueryElement.on('click', null, {rowId: jqueryElement.data('row-id')}, openAddRowPopover);
    });

    // Add an html row
    $('#add-html-row').on('click', () => {
        addRowAfter();
    });

    // Add new row after header
    $('#button-row-0').on('click', null, {rowId: 0}, openAddRowPopover);

    $('#form').on('submit', () => {
        $('tr[id*="row-"]').slice($('#with-header').is(':checked') ? 1 : 2)
            .each((rowIndex, row) => {
                $(row).children().slice(1).each((colIndex, cellContent) => {
                    $('#table-content-values').append($('<input>', {
                        type: 'text',
                        name: 'content[' + rowIndex + '][]',
                        value: JSON.stringify({
                            type: 'html',
                            value: $(cellContent).children().first().val()
                        })
                    }))
                });
            });
    })

    // display or hide header row
    function displayOrHideHeaderRow() {
        const tableContentHeader = $('#row-0');

        $('#with-header').is(':checked') ?
            tableContentHeader.css('display', 'table-row') :
            tableContentHeader.css('display', 'none');
    }

    // Add a new column after the specified column id
    function addColumnAfter(event) {
        const colIdInput = $('#col-id');
        const colId = Number(colIdInput.val()) + 1;

        const selectedColId = !!event && !!event.data && !!event.data.colId ? event.data.colId : null;

        // Create the action cell on the top of the table
        const actionCell = $('<th>', {
            'data-col-id': colId,
            class: 'sortable-column'
        }).append($('<div>', {
            class: 'table-col-actions-cell-content',
        }).append($('<div>', {
            class: 'table-col-actions-cell-content-drag'
        }).append($('<span>', {
            class: 'dashicons dashicons-editor-expand',
            title: 'Keep the mouse pressed to drag and drop the column'
        }))).append($('<div>', {
            class: 'table-col-actions-cell-content-actions'
        }).append($('<span>', {
            id: 'button-col-delete-' + colId,
            'data-col-id': colId,
            class: 'dashicons dashicons-minus action-button action-button-delete',
            title: 'Delete column'
        }).on('click', null, {colId}, deleteColumn)).append($('<span>', {
            id: 'button-col-add-' + colId,
            'data-col-id': colId,
            class: 'dashicons dashicons-plus action-button action-button-add',
            title: 'Add a column after this one',
        }).on('click', null, {colId}, addColumnAfter))));

        // Create the header cell
        const headerCell = $('<th>', {
            class: 'table-header-cell',
            'data-col-id': colId
        }).append($('<input>', {
            type: 'text',
            class: 'table-header-cell-content',
            maxLength: 255
        }));

        if (!!selectedColId) {
            actionCell.insertAfter($('#column-row-buttons>th[data-col-id="' + selectedColId + '"]'));
            headerCell.insertAfter($('#row-0>th[data-col-id="' + selectedColId + '"]'));

            $('[id*=row-]>td[data-col-id="' + selectedColId + '"]').each((index, element) => {
                $('<td>', {
                    class: 'table-content-cell',
                    'data-col-id': colId
                }).append($('<textarea>', {
                    maxLength: 255,
                    class: 'table-content-cell-content'
                })).insertAfter(element);
            });
        } else {
            $('#column-row-buttons').append(actionCell);
            $('#row-0').append(headerCell);

            // Create and add the content cells
            $('#table-content-body').children().each((index, element) => {
                $(element).append($('<td>', {
                    class: 'table-content-cell',
                    'data-col-id': colId
                }).append($('<textarea>', {
                    maxLength: 255,
                    class: 'table-content-cell-content'
                })));
            });
        }

        colIdInput.val(colId);
        initDragAndDropColumn();
    }

    // Add a new row after the current row id
    function addRowAfter() {
        const rowIdInput = $('#row-id');
        const rowId = Number(rowIdInput.val()) + 1;
        const rowIdString = rowId.toString();

        // Create the new row and place it in the table
        const tableRow = $('<tr>', {
            id: 'row-' + rowIdString
        });

        const tableContentBody = $('#table-content-body');
        if (currentRowId === 0) {
            tableContentBody.prepend(tableRow);
        } else {
            currentRowId === null || isNaN(currentRowId) ?
                tableContentBody.append(tableRow) :
                tableRow.insertAfter($('#row-' + currentRowId));
        }

        // Create actions cell with add and remove button
        tableRow.append($('<td>', {
            class: 'table-row-actions-cell sortable-row',
        }).append($('<span>', {
            class: 'dashicons dashicons-editor-expand action-button drag-row',
            title: 'Keep the mouse pressed to drag and drop the row'
        })).append($('<span>', {
            id: 'button-row-delete-' + rowIdString,
            class: 'dashicons dashicons-minus action-button action-button-delete',
            title: 'Delete row'
        }).on('click', null, {rowId: rowIdString}, deleteRow)).append($('<span>', {
            id: 'button-row-add-' + rowIdString,
            class: 'dashicons dashicons-plus action-button action-button-add',
            title: 'Add a row after this one'
        }).on('click', null, {rowId: rowIdString}, openAddRowPopover)));

        // Add n cells to complete the row
        $('.table-header-cell').each((index, element) => {
            tableRow.append($('<td>', {
                class: 'table-content-cell',
                'data-col-id': $(element).data('col-id')
            }).append($('<textarea>', {
                maxLength: 255,
                class: 'table-content-cell-content'
            })));
        });

        rowIdInput.val(rowId);
        initDragAndDropRow();
    }

    // Open the popover row creation choice next to the current row
    function openAddRowPopover(event) {
        if (!!event && !!event.data && !isNaN(event.data.rowId)) {
            const rowId = event.data.rowId;
            currentRowId = rowId;

            $('#button-row-add-' + rowId).popModal({
                html: $('#add-row-popover'),
                placement: 'rightTop'
            });
        }
    }

    // Remove the specified column
    function deleteColumn(event) {
        if (!!event && !!event.data && !!event.data.colId && $('.table-header-cell').length > 1) {
            $("[data-col-id='" + event.data.colId + "']").remove();
        }
    }

    // Remove the specified row
    function deleteRow(event) {
        if (!!event && !!event.data && !!event.data.rowId) {
            document.querySelector('#row-' + event.data.rowId).remove();
        }
    }

    // Init drag and drop column options
    function initDragAndDropColumn() {
        if (!!columnDragger) {
            columnDragger.destroy();
        }

        const tableContent = document.getElementById('table-content');
        columnDragger = tableDragger.default(tableContent, {
            dragHandler: ".sortable-column"
        });
    }

    // Init drag and drop row options
    function initDragAndDropRow() {
        if (!!rowDragger) {
            rowDragger.destroy();
        }

        if ($('.sortable-row').length > 0) {
            const tableContent = document.getElementById('table-content');
            rowDragger = tableDragger.default(tableContent, {
                dragHandler: ".sortable-row",
                mode: 'row',
                onlyBody: true
            });
        }
    }
})