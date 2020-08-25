jQuery(($) => {
    localStorage.setItem('affiliation-table-row-id', '0');
    localStorage.setItem('affiliation-table-current-row-id', '-1');

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
        localStorage.setItem('affiliation-table-current-row-id', null);

        $('#add-row-after-last').popModal({
            html: $('#add-row-popover'),
            placement: 'rightTop'
        });
    });

    $('#add-html-row').on('click', () => {
        addRowAfter();
    });

    // Add new row after header
    $('#button-row-0').on('click', null, {rowId: 0}, openAddRowPopover);

    // Add a new row after the current row id
    function addRowAfter() {
        const newId = (Number(localStorage.getItem('affiliation-table-row-id')) + 1).toString();
        localStorage.setItem('affiliation-table-row-id', newId);

        // Create the new row and place it in the table
        const tableRow = $('<tr>', {
            id: 'row-' + newId
        });

        const tableContentBody = $('.table-content-body');
        const currentRowId = Number(localStorage.getItem('affiliation-table-current-row-id'));
        if (currentRowId === 0) {
            tableContentBody.prepend(tableRow);
        }  else {
            currentRowId === null || isNaN(currentRowId) ?
                tableContentBody.append(tableRow) :
                tableRow.insertAfter($('#row-' + currentRowId));
        }

        // Create actions cell with add and remove button
        tableRow.append($('<td>', {
            class: 'table-cell-actions',
        }).append($('<span>', {
            class: 'dashicons dashicons-minus action-button action-button-delete',
            title: 'Delete row'
        }).on('click', null, {rowId: newId}, deleteRow)).append($('<span>', {
            id: 'button-row-' + newId,
            class: 'dashicons dashicons-plus action-button action-button-add',
            title: 'Add a row after this one'
        }).on('click', null, {rowId: newId}, openAddRowPopover)));

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

    // Open the popover row creation choice next to the current row
    function openAddRowPopover(event) {
        if (!!event && !!event.data && !isNaN(event.data.rowId)) {
            const rowId = event.data.rowId;
            localStorage.setItem('affiliation-table-current-row-id', rowId);

            $('#button-row-' + rowId).popModal({
                html: $('#add-row-popover'),
                placement: 'rightTop'
            });
        }
    }

    function deleteRow(event) {
        if (!!event && !!event.data && !!event.data.rowId) {
            document.querySelector('#row-' + event.data.rowId).remove();
        }
    }
})