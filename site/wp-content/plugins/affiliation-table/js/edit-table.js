jQuery(($) => {
    const HTML = 'HTML';
    const AFFILIATION = 'AFFILIATION';
    const IMAGE = 'IMAGE';

    let lastCellId = $('#last-cell-id').val();

    let currentRowId = null;
    let currentCellId = null;
    let currentAffiliateLinkId = null;
    let currentAffiliationUrl = '';

    let columnDragger = null;
    let rowDragger = null;

    displayOrHideHeaderRow();
    updateHeaderStyle();
    initDragAndDropColumn();
    initDragAndDropRow();
    initAddAffiliateLinkButtons();
    initSelectImageButtons();
    initRemoveImagesButtons();
    initEditAffiliateLinkButtons();
    addRecaluclationLinkEvents();

    //init color pickers
    $('#header-background-color').minicolors({});
    $('#header-text-color').minicolors({});
    $('#link-background-color').minicolors({});
    $('#link-text-color').minicolors({});

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

    // Open the header edition options modal
    $('#edit-header-options').on('click', () => {
       openEditHeaderOptionsModal();
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

    // Add html row event
    $('#add-html-row').on('click', () => {
        addRowAfter(HTML);
    });

    // Add affiliation row event
    $('#add-affiliation-row').on('click', () => {
        addRowAfter(AFFILIATION);
    });

    // Add image row event
    $('#add-image-row').on('click', () => {
        addRowAfter(IMAGE);
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
                    }));
                });
            });

        $('#header-options').val(JSON.stringify({
            background: $('#header-background-color').val(),
            color: $('#header-text-color').val(),
            'font-weight': $('#header-font-weight').val(),
            'font-size': $('#header-font-size').val()
        }));
    });

    // Init variable parameters when webshop change (edition modal)
    $('#webshop-select').on('change', null, null, () => {
        initAffiliateLinkInputsModal();
    });

    // display or hide header row
    function displayOrHideHeaderRow() {
        const editHeaderOptions = $('#edit-header-options');
        const tableContentHeader = $('#row-0');

        if ($('#with-header').is(':checked')) {
            editHeaderOptions.css('display', 'table-row');
            tableContentHeader.css('display', 'table-row');
        } else {
            editHeaderOptions.css('display', 'none');
            tableContentHeader.css('display', 'none');
        }
    }

    // Update header style : background color and text color
    function updateHeaderStyle() {
        const background = $('#header-background-color').val();
        $('.table-header-cell').css('background', background);
        $('.table-header-cell-content')
            .css('background', background)
            .css('color', $('#header-text-color').val())
            .css('font-weight', $('#header-font-weight').val())
            .css('font-size', $('#header-font-size').val());
    }

    // Add a new column after the specified column id
    function addColumnAfter(event) {
        const colIdInput = $('#col-id');
        const colId = Number(colIdInput.val()) + 1;

        const selectedColId = !!event && !!event.data && !!event.data.colId ? event.data.colId : null;

        // Create the action cell on the top of the table
        const actionCell = $('<th>', {
            'data-col-id': colId,
            class: 'sortable-column table-col-actions-cell'
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
            class: 'dashicons dashicons-minus action-button-delete pointer',
            title: 'Delete column'
        }).on('click', null, {colId}, deleteColumn)).append($('<span>', {
            id: 'button-col-add-' + colId,
            'data-col-id': colId,
            class: 'dashicons dashicons-plus action-button-add pointer',
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
                $(element).append(makeCell($(element).children().last().data('cell-type'), colId));
            });
        }

        colIdInput.val(colId);
        initDragAndDropColumn();
        updateHeaderStyle();
    }

    // Add a new row after the current row id
    function addRowAfter(type) {
        const rowIdInput = $('#row-id');
        const rowId = Number(rowIdInput.val()) + 1;
        const rowIdString = rowId.toString();

        // Create the new row and place it in the table
        const tableRow = $('<tr>', {
            id: 'row-' + rowIdString,
            class: 'sortable-row'
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
            class: 'table-row-actions-cell',
        }).append($('<span>', {
            class: 'dashicons dashicons-editor-expand drag-row',
            title: 'Keep the mouse pressed to drag and drop the row'
        })).append($('<span>', {
            id: 'button-row-delete-' + rowIdString,
            class: 'dashicons dashicons-minus action-button-delete pointer',
            title: 'Delete row'
        }).on('click', null, {rowId: rowIdString}, deleteRow)).append($('<span>', {
            id: 'button-row-add-' + rowIdString,
            class: 'dashicons dashicons-plus action-button-add pointer',
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
            case IMAGE :
                return $('<td>', {
                    id: 'cell-' + lastCellId,
                    class: 'table-content-cell-image',
                    'data-col-id': colId,
                    'data-cell-type': IMAGE
                })
                    .append($('<input>', {
                        id: 'cell-content-' + lastCellId,
                        name: 'cell-content-' + lastCellId,
                        type: 'hidden',
                        value: '',
                    }))
                    .append($('<span>', {
                        id: 'select-image-button-' + lastCellId,
                        class: 'dashicons dashicons-edit action-button-add pointer',
                        title: 'Select image'
                    }).on('click', null, {cellId: lastCellId}, openSelectImageModal))
                    .append($('<div>', {
                        id: 'table-content-cell-image-overview-' + lastCellId,
                        class: 'table-content-cell-image-overview'
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
                        class: 'dashicons dashicons-plus add-affiliation-link-button action-button-add pointer',
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

    // Open modal to select image
    function openSelectImageModal(event) {
        if (!!event && !!event.data && !!event.data.cellId) {
            let frame = wp.media({
                title: 'Select or Upload new image',
                button: {
                    text: 'Validate'
                },
                multiple: false
            });

            frame.on('select', () => {
                const attachment = frame.state('').get('selection').first().toJSON();

                const cellId = event.data.cellId;
                $('#table-content-cell-image-overview-' + cellId)
                    .empty()
                    .append($('<img>', {
                        src: attachment.url,
                        alt: attachment.alt,
                        class: 'table-content-cell-image-overview-content'
                    }));

                if ($('#remove-image-button-' + cellId).length === 0) {
                    $($('<span>', {
                        id: 'remove-image-button-' + cellId,
                        class: 'dashicons dashicons-minus remove-image-button action-button-delete pointer',
                        title: 'Remove image',
                        'data-cell-id': cellId
                    })
                        .on('click', null, {cellId}, removeImage))
                        .insertAfter('#select-image-button-' + cellId);
                }

                $('#cell-content-' + cellId).val(`<img src='${attachment.url}' alt='${attachment.alt}'>`);
            });

            frame.open();
        }
    }

    // Remove image for the selected cell
    function removeImage(event) {
        if (!!event && !!event.data && !!event.data.cellId) {
            const cellId = event.data.cellId
            $('#cell-content-' + cellId).val(null);
            $('#remove-image-button-' + cellId).remove();
            $('#table-content-cell-image-overview-' + cellId).empty();
        }
    }

    // Open modal which edit affiliation link
    function openEditAffiliationLinkModal(event) {
        if (!!event && !!event.data && !isNaN(event.data.cellId)) {
            currentCellId = event.data.cellId;
            currentAffiliateLinkId = event.data.id;

            let affilitateLinkValue = !!id ? JSON.parse($('#cell-content-' + currentCellId).val())
                .find(affilitateLinkValue => affilitateLinkValue.id === currentAffiliateLinkId) : null;

            if (!affilitateLinkValue) {
                $('#webshop-select option:first').prop('selected', true);
            }

            initAffiliateLinkInputsModal(affilitateLinkValue);

            const cancel = () => function () {
                $(this).dialog('close');
            }

            const buttons = !!affilitateLinkValue ? {
                'Edit': updateAffiliateLink,
                'Remove': removeAffiliateLink,
                'Cancel': cancel()
            } : {
                'Add': addAffiliateLink,
                'Cancel': cancel()
            };

            $('#edit-affiliation-link-modal').dialog({
                resizable: true,
                minWidth: 400,
                title: !!affilitateLinkValue ? 'Edit affiliation link' : 'Create affiliation link',
                modal: true,
                buttons,
            });
        }
    }

    // Open modal wich edit headerOptions
    function openEditHeaderOptionsModal() {
        $('#edit-header-options-modal').dialog({
            resizable: true,
            minWidth: 450,
            minHeight : 400,
            title: 'Edit header options',
            modal: true,
            buttons: {
                'Cancel': function() {
                    $(this).dialog('close');
                },
                'Edit': function() {
                    updateHeaderStyle();
                    $(this).dialog('close');
                }
            },
        });
    }

    // Add new affiliate link in the selected cell
    function addAffiliateLink() {
        const id = Date.now();
        const value = makeAffiliationLinkValue(id);

        const cellContent = JSON.parse($('#cell-content-' + currentCellId).val());
        cellContent.push(value);

        $('#cell-content-' + currentCellId).val(JSON.stringify(cellContent));

        $('#cell-content-link-list-' + currentCellId).append($('<button>', {
            type: 'button',
            class: 'cell-content-link-list-button',
            style: getAffiliateLinkStyle(value.background, value.color),
            title: 'Edit affiliate link',
            'data-id': id
        }).on('click', null, {cellId: currentCellId, id}, openEditAffiliationLinkModal)
            .append($('<span>', {
                class: 'dashicons dashicons-cart cell-content-link-list-icon'
            }))
            .append($('<span>', {
                text: value.linkText
            })));

        $(this).dialog('close');
    }

    // Update selected affiliate link
    function updateAffiliateLink() {
        const affiliateLinkValues = JSON.parse($('#cell-content-' + currentCellId).val())
            .map(affiliateLinkValue => affiliateLinkValue.id === currentAffiliateLinkId ?
                makeAffiliationLinkValue(affiliateLinkValue.id) :
                affiliateLinkValue);

        $('#cell-content-' + currentCellId).val(JSON.stringify(affiliateLinkValues));

        $(`.cell-content-link-list-button[data-id="${currentAffiliateLinkId}"]`)
            .attr('style', getAffiliateLinkStyle($('#link-background-color').val(), $('#link-text-color').val()))
            .empty()
            .append($('<span>', {
                class: 'dashicons dashicons-cart cell-content-link-list-icon'
            }))
            .append($('<span>', {
                text: $('#link-text-input').val()
            }));

        $(this).dialog('close');
    }

     // extract background color and color from affiliate link if parameters exists and return them as string
    function getAffiliateLinkStyle(background, color) {
        if (!background && !color) {
            return null;
        }

        let style = null;
        if (!!background) {
            style = 'background:' + background;
        }

        if (color) {
            style = style + (!!style ? ';' : '') + 'color:' + color;
        }

        return style;
    }

    // Remove selected affiliate link
    function removeAffiliateLink() {
        $('#cell-content-' + currentCellId).val(JSON.stringify(JSON.parse($('#cell-content-' + currentCellId).val())
            .filter(affilitateLinkValue => affilitateLinkValue.id !== currentAffiliateLinkId)));

        $(`.cell-content-link-list-button[data-id="${currentAffiliateLinkId}"]`).remove();
        $(this).dialog('close');
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
            dragHandler: ".sortable-column",
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

    // Add event initSelectImageButtons on each "Remove image" buttons
    function initSelectImageButtons() {
        $('.select-image-button').each((index, element) => {
            const jqueryElement = $(element);
            jqueryElement.on(
                'click',
                null,
                {cellId: jqueryElement.data('cell-id')},
                openSelectImageModal);
        });
    }

    // Add event initRemoveImagesButtons on each "" buttons
    function initRemoveImagesButtons() {
        $('.remove-image-button').each((index, element) => {
            const jqueryElement = $(element);
            jqueryElement.on(
                'click',
                null,
                {cellId: jqueryElement.data('cell-id')},
                removeImage);
        });
    }

    // Add event openEditAffiliationLinkModal on each "Edit affiliation link" buttons
    function initEditAffiliateLinkButtons() {
        $('.cell-content-link-list-button').each((index, element) => {
            const jqueryElement = $(element);

            jqueryElement.on(
                'click',
                null,
                {cellId: jqueryElement.data('cell-id'), id: jqueryElement.data('id')},
                openEditAffiliationLinkModal);
        });
    }

    // Add recalulation link overview on each webshop parameter
    function addRecaluclationLinkEvents() {
        $('.affiliation-parameter-input').on('change keyup paste', null, {}, () => {
            recalculateAffiliationLinkOverview();
        })
    }

    // Clear and add preferences / selected values in the edit affiliation links modal depending on the selected webshop
    function initAffiliateLinkInputsModal(affilitateLinkValue) {
        let selectedWebshop = $("#webshop-select option:selected");

        if (!!affilitateLinkValue) {
            $('#webshop-select').val(affilitateLinkValue.webshopId);
            selectedWebshop = $("#webshop-select option:selected");

            updateAffiateInputsModal(
                affilitateLinkValue.linkText,
                affilitateLinkValue.background,
                affilitateLinkValue.color);
        } else {
            updateAffiateInputsModal(
                selectedWebshop.data('linkTextPreference'),
                selectedWebshop.data('backgroundColorPreference'),
                selectedWebshop.data('textColorPreference'));
        }

        currentAffiliationUrl = selectedWebshop.data('url');

        $('.affiliation-parameter-row').remove();
        selectedWebshop.data('parameters')
            .split('|||')
            .reverse()
            .forEach(parameter => $('#link-text-color-row').after($('<tr>', {
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

        $('.affiliation-parameter-input').each((index, element) => {
            const jqueryElement = $(element);
            jqueryElement.val(!!affilitateLinkValue ? affilitateLinkValue[jqueryElement.data('parameter')] : '');
        });

        addRecaluclationLinkEvents();
        recalculateAffiliationLinkOverview();
    }

    // Update editAffiliationLinkModalInputs (preferences if new, else filled values)
    function updateAffiateInputsModal(linkText, background, color) {
        $('#link-text-input').val(linkText);

        const backgroundColorInput = $('#link-background-color');
        backgroundColorInput.val(background);
        backgroundColorInput.next().children().css('background-color', background);

        const textColorInput = $('#link-text-color');
        textColorInput.val(color);
        textColorInput.next().children().css('background-color', color);
    }

    // Make affiliation link value depending on the content modal
    function makeAffiliationLinkValue(id) {
        const value = {
            id,
            url: $('#affiliation-link-overview').text(),
            background: $('#link-background-color').val(),
            color: $('#link-text-color').val()
        }

        $('.affiliation-parameter-input').each(((index, element) => {
            const jqueryElement = $(element);
            value[jqueryElement.data('parameter')] = jqueryElement.val();
        }));

        value['webshopId'] = Number($("#webshop-select").val());
        value['linkText'] = $('#link-text-input').val();

        return value;
    }

    // Recalculate affiliation link overview depending on modal parameters
    function recalculateAffiliationLinkOverview() {
        $('.affiliation-parameter-input').each(() => {
            let url = currentAffiliationUrl;

            $('.affiliation-parameter-input').each((index, element) => {
                const jqueryElement = $(element);
                const value = jqueryElement.val();
                if (!!value) {
                    url = url.replace(`[[${jqueryElement.data('parameter')}]]`, value.toString());
                }
            });

            $('#affiliation-link-overview').text(url);
        })
    }
});