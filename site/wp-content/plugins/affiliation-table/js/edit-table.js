jQuery(($) => {
    const HTML = 'HTML';
    const AFFILIATION = 'AFFILIATION';

    let lastCellId = $('#last-cell-id').val();
    let affiliationUrl = $('#affiliation-url').val();

    let currentRowId = null;
    let currentCellId = null;

    let columnDragger = null;
    let rowDragger = null;

    displayOrHideHeaderRow();
    initDragAndDropColumn();
    initDragAndDropRow();
    initAddAffiliateLinkButtons();
    addRecaluclationLinkEvents();

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
        addRowAfter(HTML);
    });

    $('#add-affiliation-row').on('click', () => {
        addRowAfter(AFFILIATION);
    });

    // Add new row after header
    $('#button-row-0').on('click', null, {rowId: 0}, openAddRowPopover);

    $('#form').on('submit', () => {
        $('tr[id*="row-"]').slice($('#with-header').is(':checked') ? 1 : 2)
            .each((rowIndex, row) => {
                $(row).children().slice(1).each((colIndex, cellContent) => {
                    const jqueryElement = $(cellContent);

                    $('#table-content-values').append($('<input>', {
                        type: 'text',
                        name: 'content[' + rowIndex + '][]',
                        value: JSON.stringify({
                            type: jqueryElement.data('cell-type'),
                            value: jqueryElement.children().first().val()
                        })
                    }))
                });
            });
    });

    // Init variable parameters when webshop change (edition modal)
    $('#webshop-select').on('change', null, null, () => {
        const selectedWebshop = $("#webshop-select option:selected");
        affiliationUrl = selectedWebshop.data('url');

        $('#link-text-input').val(selectedWebshop.text().trim());

        $('.affiliation-parameter-row').remove();

        selectedWebshop.data('parameters')
            .split('|||')
            .reverse()
            .forEach(parameter => $('#link-text-row').after($('<tr>', {
                class: 'affiliation-parameter-row',
            })
                .append($('<th>', {
                    scope: 'row'
                }).append(`<label>${parameter}</label>`))
                .append($('<td>').append($('<input>', {
                    type: 'text',
                    class: 'affiliation-parameter-input',
                    maxLength: 255,
                    'data-parameter': parameter
                })))));

        $('#affiliation-link-overview').text(affiliationUrl);
        addRecaluclationLinkEvents();
    });

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
                makeCell($(element).data('cell-type'), colId).insertAfter(element);
            });
        } else {
            $('#column-row-buttons').append(actionCell);
            $('#row-0').append(headerCell);

            // Create and add the content cells
            $('#table-content-body').children().each((index, element) => {
                $(element).append(makeCell($(element).data('cell-type'), colId));
            });
        }

        colIdInput.val(colId);
        initDragAndDropColumn();
    }

    // Add a new row after the current row id
    function addRowAfter(type) {
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

        // Add cells to complete the row
        $('.table-header-cell').each((index, element) => {
            tableRow.append(makeCell(type, $(element).data('col-id')));
        });

        rowIdInput.val(rowId);
        initDragAndDropRow();
    }

    // Create cell depending on the type
    function makeCell(type, colId) {
        lastCellId++;

        switch (type) {
            case HTML :
                return $('<td>', {
                    id: 'cell-' + lastCellId,
                    class: 'table-content-cell-html',
                    'data-col-id': colId,
                    'data-cell-type': HTML,
                }).append($('<textarea>', {
                    maxLength: 255,
                    class: 'table-content-cell-html-content'
                }));
            case AFFILIATION:
                return $('<td>', {
                    id: 'cell-' + lastCellId,
                    class: 'table-content-cell-affiliation',
                    'data-col-id': colId,
                    'data-cell-type': AFFILIATION
                })
                    .append($('<input>', {
                        id: 'cell-content-' + lastCellId,
                        name: 'cell-content-' + lastCellId,
                        type: 'hidden',
                        value: '[]',
                    }))
                    .append($('<span>', {
                        class: 'dashicons dashicons-plus add-affiliation-link-button action-button-add',
                        title: 'Add affiliate link'
                    }).on('click', null, {cellId: lastCellId}, openEditAffiliationLinkModal))
                    .append($('<div>', {
                        id: 'cell-content-link-list-' + lastCellId
                    }));
            default:
                return $('<td>', {
                    class: 'table-content-cell-unknown',
                    'data-col-id': colId
                }).append('Unknown type');
        }
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

    function openEditAffiliationLinkModal(event) {
        if (!!event && !!event.data && !isNaN(event.data.cellId)) {
            $('.affiliation-parameter-input').each((index, element) => {
                $(element).val('');
            });

            $('#affiliation-link-overview').text(affiliationUrl);
            $('#link-text-input').val($('#webshop-select option:selected').text().trim());

            currentCellId = event.data.cellId;

            $('#edit-affiliation-link-modal').dialog({
                resizable: true,
                minWidth: 400,
                buttons: {
                    'Add': function () {
                        const value = {
                            url: $('#affiliation-link-overview').text()
                        }

                        $('.affiliation-parameter-input').each(((index, element) => {
                            const jqueryElement = $(element);
                            value[jqueryElement.data('parameter')] = jqueryElement.val();
                        }));

                        const selectedOption = $("#webshop-select");
                        value['webshopId'] = Number(selectedOption.val());

                        const textInput = $('#link-text-input').val();
                        value['linkText'] = textInput;

                        const cellContent = JSON.parse($('#cell-content-' + currentCellId).val());
                        cellContent.push(value);

                        $('#cell-content-' + currentCellId).val(JSON.stringify(cellContent));

                        $('#cell-content-link-list-' + currentCellId).append($('<button>', {
                            type: 'button',
                            class: 'button-primary cell-content-link-list-button',
                            title: 'Edit affiliate link'
                        })
                            .append($('<span>', {
                                class: 'dashicons dashicons-cart cell-content-link-list-icon'
                            }))
                            .append($('<span>', {
                                text: textInput
                            })));

                        $(this).dialog('close');
                    },
                    'Close': function () {
                        $(this).dialog('close');
                    }
                }
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

    // Add event openEditAffiliationLinkModal on each "Add affiliation link" button
    function initAddAffiliateLinkButtons() {
        $('.add-affiliation-link-button').each((index, element) => {
            const jqueryElement = $(element);
            jqueryElement.on(
                'click',
                null,
                {cellId: jqueryElement.data('cell-id')},
                openEditAffiliationLinkModal);
        });
    }

    // Add recalulation link on each webshop parameter
    function addRecaluclationLinkEvents() {
        $('.affiliation-parameter-input').on('change keyup paste', null, {}, () => {
            let url = affiliationUrl;

            $('.affiliation-parameter-input').each((index, element) => {
                const jqueryElement = $(element);

                url = url.replace(`[[${jqueryElement.data('parameter')}]]`, jqueryElement.val());
            });

            $('#affiliation-link-overview').text(url);
        })
    }
});