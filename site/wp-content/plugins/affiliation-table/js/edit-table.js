jQuery(($) => {
    localStorage.setItem('affiliation-table-row-id', '0');

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

    // Hide or remove header row
    $('#with-header').on('change', () => {
        const tableContentHeader = $('.table-content-header');

        $('#with-header').is(':checked') ?
            tableContentHeader.css('display', 'table-row-group') :
            tableContentHeader.css('display', 'none');
    });

    // Add new row after last table row
    $('#add-row-after-last').on('click', () => {
        addRowAfter();
    });

    // Add new row after header
    $('#add-row-after-header').on('click', null, {rowId: 0}, addRowAfter);

    /**
     * Add a new row after the row specified by its id
     * @param event
     */
    function addRowAfter(event) {
        const newId = (Number(localStorage.getItem('affiliation-table-row-id')) + 1).toString();
        localStorage.setItem('affiliation-table-row-id', newId);

        // Create the new row and place it in the table
        const tableRow = $('<tr>', {
            id: 'row-' + newId
        });

        const tableContentBody = $('.table-content-body');
        const rowId = !!event && !!event.data && (event.data.rowId || event.data.rowId === 0) ? event.data.rowId : null;
        if (rowId === 0) {
            tableContentBody.prepend(tableRow);
        } else {
            rowId === null || isNaN(rowId) ? tableContentBody.append(tableRow) : tableRow.insertAfter($('#row-' + rowId));
        }

        // Create actions cell with add and remove button
        tableRow.append($('<td>', {
            class: 'table-cell-actions',
        }).append($('<span>', {
            class: 'dashicons dashicons-minus action-button action-button-delete',
            title: 'Delete row'
        }).on('click', null, {rowId: newId}, deleteRow)).append($('<span>', {
            class: 'dashicons dashicons-plus action-button action-button-add',
            title: 'Add row'
        }).on('click', null, {rowId: newId}, addRowAfter)));

        // Add n cells to complete the row
        for (let i = 0; i < 4; i++) {
            tableRow.append($('<td>', {
                class: 'table-content-cell',
            }).append($('<textarea>', {
                maxLength: 255,
                class: 'table-content-cell-content'
            })));
        }
    }

    function deleteRow(event) {
        if (!!event && !!event.data && !!event.data.rowId) {
            document.querySelector('#row-' + event.data.rowId).remove();
        }
    }
})