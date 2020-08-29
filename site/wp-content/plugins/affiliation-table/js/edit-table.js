jQuery(($) => {
    let tableRowId = 0;
    let currentRowId = null;
    let columnNumber = 4;

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

    // Add delete col event
    $("[id*=button-col-delete-]").each((index, element) => {
        const jqueryElement = $(element);
        jqueryElement.on('click', null, {colNumber: jqueryElement.data('col-number')}, deleteColumn);
    });

    // Add add col event
    $("[id*=button-col-add-]").each((index, element) => {
        const jqueryElement = $(element);
        jqueryElement.on('click', null, {colNumber: jqueryElement.data('col-number')}, addColumnAfter);
    });

    // Add an html row
    $('#add-html-row').on('click', () => {
        addRowAfter();
    });

    // Add new row after header
    $('#button-row-0').on('click', null, {rowId: 0}, openAddRowPopover);

    // display or hide header row
    function displayOrHideHeaderRow() {
        const tableContentHeader = $('#row-0');

        $('#with-header').is(':checked') ?
            tableContentHeader.css('display', 'table-row') :
            tableContentHeader.css('display', 'none');
    }

    // Add a new column after the specified column number
    function addColumnAfter(event) {
        columnNumber += 1;

        const selectedColNumber = !!event && !!event.data && !!event.data.colNumber ? event.data.colNumber : null;

        // Create the action cell on the top of the table
        const actionCell = $('<th>', {
            'data-col-number': columnNumber,
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
            id: 'button-col-delete-' + columnNumber,
            'data-col-number': columnNumber,
            class: 'dashicons dashicons-minus action-button action-button-delete',
            title: 'Delete column'
        }).on('click', null, {colNumber: columnNumber}, deleteColumn)).append($('<span>', {
            id: 'button-col-add-' + columnNumber,
            'data-col-number': columnNumber,
            class: 'dashicons dashicons-plus action-button action-button-add',
            title: 'Add a column after this one',
        }).on('click', null, {columnNumber: columnNumber}, addColumnAfter))));

        // Create the header cell
        const headerCell = $('<th>', {
            class: 'table-header-cell',
            'data-col-number': columnNumber
        }).append($('<input>', {
            type: 'text',
            class: 'table-header-cell-content',
            maxLength: 255
        }));

        if (!!selectedColNumber) {
            actionCell.insertAfter($('#column-row-buttons>th[data-col-number="' + selectedColNumber + '"]'));
            headerCell.insertAfter($('#row-0>th[data-col-number="' + selectedColNumber + '"]'));

            $('[id*=row-]>td[data-col-number="' + selectedColNumber + '"]').each((index, element) => {
                $('<td>', {
                    class: 'table-content-cell',
                    'data-col-number': columnNumber
                }).append($('<textarea>', {
                    maxLength: 255,
                    class: 'table-content-cell-content'
                })).insertAfter(element);
            });

            reallocateColumnNumbers();
        } else {
            $('#column-row-buttons').append(actionCell);
            $('#row-0').append(headerCell);

            // Create and add the content cells
            $('.table-content-body').children().each((index, element) => {
                $(element).append($('<td>', {
                    class: 'table-content-cell',
                    'data-col-number': columnNumber
                }).append($('<textarea>', {
                    maxLength: 255,
                    class: 'table-content-cell-content'
                })));
            });
        }

        initDragAndDropColumn();
    }

    // Add a new row after the current row id
    function addRowAfter() {
        tableRowId += 1;
        const tableRowIdString = tableRowId.toString();

        // Create the new row and place it in the table
        const tableRow = $('<tr>', {
            id: 'row-' + tableRowIdString
        });

        const tableContentBody = $('.table-content-body');
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
            class: 'dashicons dashicons-minus action-button action-button-delete',
            title: 'Delete row'
        }).on('click', null, {rowId: tableRowIdString}, deleteRow)).append($('<span>', {
            id: 'button-row-' + tableRowIdString,
            class: 'dashicons dashicons-plus action-button action-button-add',
            title: 'Add a row after this one'
        }).on('click', null, {rowId: tableRowIdString}, openAddRowPopover)));

        // Add n cells to complete the row
        for (let i = 1; i <= columnNumber; i++) {
            tableRow.append($('<td>', {
                class: 'table-content-cell',
                'data-col-number': i
            }).append($('<textarea>', {
                maxLength: 255,
                class: 'table-content-cell-content'
            })));
        }

        initDragAndDropRow();
    }

    // Open the popover row creation choice next to the current row
    function openAddRowPopover(event) {
        if (!!event && !!event.data && !isNaN(event.data.rowId)) {
            const rowId = event.data.rowId;
            currentRowId = rowId;

            $('#button-row-' + rowId).popModal({
                html: $('#add-row-popover'),
                placement: 'rightTop'
            });
        }
    }

    // Remove the specified column
    function deleteColumn(event) {
        if (!!event && !!event.data && !!event.data.colNumber && columnNumber > 1) {
            $("[data-col-number='" + event.data.colNumber + "']").remove();

            columnNumber -= 1;

            reallocateColumnNumbers();
        }
    }

    // Reallocate the column numbers on each element which contains this information
    function reallocateColumnNumbers() {
        $('#column-row-buttons').children().each((index, element) => {
            const jqueryElement = $(element);
            jqueryElement.attr('data-col-number', index);
        });

        $("[id*=button-col-delete-]").each((index, element) => {
            index += 1;
            const jqueryElement = $(element);

            jqueryElement
                .attr('id', 'button-col-delete-' + index)
                .attr('data-col-number', index)
                .off()
                .on('click', null, {colNumber: index}, deleteColumn);
        });

        $("[id*=button-col-add-]").each((index, element) => {
            index += 1;
            const jqueryElement = $(element);

            jqueryElement
                .attr('id', 'button-col-add-' + index)
                .attr('data-col-number', index)
                .off()
                .on('click', null, {colNumber: index}, addColumnAfter);
        });

        $('[id*=row-]').each((index, element) => {
            $(element).children().each((index, element) => {
                $(element).attr('data-col-number', index);
            });
        })
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

        columnDragger.on('drop', () => {
            reallocateColumnNumbers();
        });
    }

    // Init drag and drop row options
    function initDragAndDropRow() {
        if (!!rowDragger) {
            rowDragger.destroy();
        }

        if($('.sortable-row').length > 0) {
            const tableContent = document.getElementById('table-content');
            rowDragger = tableDragger.default(tableContent, {
                dragHandler: ".sortable-row",
                mode: 'row',
                onlyBody: true
            });
        }
    }
})