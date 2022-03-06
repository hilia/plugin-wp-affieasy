jQuery(($) => {
    const HTML = 'HTML';
    const AFFILIATION = 'AFFILIATION';
    const IMAGE = 'IMAGE';

    let lastCellId = $('#last-cell-id').val();
    let hasNoWebshop = $('#has-no-webshop').val();

    let currentRowId = null;
    let currentCellId = null;
    let currentAffiliateLinkId = null;
    let currentAffiliationUrl = '';

    let columnDragger = null;
    let rowDragger = null;

    let canUsePremiumCode = $('#can-use-premium-code').val() === '1';

    // Force initial col-id
    $('#col-id').val($('.table-header-cell-content').size());

    // init header type value
    $('#header-type').val($('#initial-header-type').val());

    displayOrHideHeaders();
    updateHeaderStyle();
    initDragAndDropColumn();
    initDragAndDropRow();
    initAddAffiliateLinkButtons();
    initSelectImageButtons();
    initRemoveImagesButtons();
    initEditAffiliateLinkButtons();
    addRecalculationLinkEvents();

    //init color pickers
    $('#background-color').minicolors({});
    $('#header-column-background').minicolors({});
    $('#header-column-color').minicolors({});
    $('#header-row-background').minicolors({});
    $('#header-row-color').minicolors({});
    $('#link-background-color').minicolors({});
    $('#link-text-color').minicolors({});

    if (!canUsePremiumCode) {
        $('#max-width').prop( "disabled", true);
        $('#responsive-breakpoint').prop( "disabled", true);
        $('#background-color').prop( "disabled", true);
    }

    // Add hide or display headers event
    $('#header-type').on('change', () => {
        displayOrHideHeaders();
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

    // Open the tips popovers
    $('#show-tips').on('click', () => {
        $('#show-tips').popModal({
            html: $('#show-tips-popover'),
            placement: 'bottomCenter'
        });
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
        const headerType = $('#header-type').val();

        $('#max-width').prop( "disabled", false);
        $('#responsive-breakpoint').prop( "disabled", false);
        $('#background-color').prop( "disabled", false);

        $('tr[id*="row-"]').slice(['COLUMN_HEADER', 'BOTH'].includes(headerType) ? 1 : 2)
            .each((rowIndex, row) => {
                $(row).children().slice(['ROW_HEADER', 'BOTH'].includes(headerType) ? 1 : 2)
                    .each((colIndex, cellContent) => {
                        const jqueryElement = $(cellContent);

                        if (!jqueryElement.hasClass('header-without-value')) {
                            $('#table-content-values').append($('<input>', {
                                type: 'text',
                                name: 'content[' + rowIndex + '][]',
                                value: JSON.stringify({
                                    type: jqueryElement.data('cell-type'),
                                    value: jqueryElement.children().first().val()
                                })
                            }));
                        }
                    });
            });
    });

    // Init variable parameters when webshop change (edition modal)
    $('#webshop-select').on('change', null, null, () => {
        initAffiliateLinkInputsModal();
    });

    // display or hide headers depending on selection
    function displayOrHideHeaders() {
        const headerType = $('#header-type').val();

        const headerColumnRow = $('#row-0');
        const editHeaderOptionsModalColumnOptions = $('#edit-header-options-modal-column-options');
        if (['COLUMN_HEADER', 'BOTH'].includes(headerType)) {
            headerColumnRow.show();
            editHeaderOptionsModalColumnOptions.show();
        } else {
            headerColumnRow.hide();
            editHeaderOptionsModalColumnOptions.hide();
        }

        const headerRowsCells = $('.table-content-header-row');
        const editHeaderOptionsModalRowOptions = $('#edit-header-options-modal-row-options');
        if (['ROW_HEADER', 'BOTH'].includes(headerType)) {
            headerRowsCells.show();
            editHeaderOptionsModalRowOptions.show();
        } else {
            headerRowsCells.hide();
            editHeaderOptionsModalRowOptions.hide();
        }

        const headerOptionsButton = $('#edit-header-options');
        if (headerType !== 'NONE') {
            headerOptionsButton.show();
        } else {
            headerOptionsButton.hide();
        }
    }

    // Update headers styles : background color, text color, font weight, and font size (for column and row headers)
    function updateHeaderStyle() {
        // Column header options
        const columnBackground = $('#header-column-background').val();
        $('.table-header-cell:not(.without-value)').css('background', columnBackground);
        $('.table-header-cell-content:not(.without-value)')
            .css('background', columnBackground)
            .css('color', $('#header-column-color').val())
            .css('font-weight', $('#header-column-font-weight').val())
            .css('font-size', $('#header-column-font-size').val());

        // Row header options
        const rowBackground = $('#header-row-background').val();
        $('.table-content-header-row:not(.without-value)').css('background', rowBackground);
        $('.table-header-row-cell-content:not(.without-value)')
            .css('background', rowBackground)
            .css('color', $('#header-row-color').val())
            .css('font-weight', $('#header-row-font-weight').val())
            .css('font-size', $('#header-row-font-size').val());
    }

    // Add a new column after the specified column id
    function addColumnAfter(event) {
        const colIdInput = $('#col-id');
        const colId = Number(colIdInput.val()) + 1;

        const selectedColId = !!event && !!event.data && (!!event.data.colId || event.data.colId === 0) ?
            event.data.colId :
            null;

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
            title: translations.dragAndDropColumn
        }))).append($('<div>', {
            class: 'table-col-actions-cell-content-actions'
        }).append($('<span>', {
            id: 'button-col-delete-' + colId,
            'data-col-id': colId,
            class: 'dashicons dashicons-minus action-button-delete pointer',
            title: translations.deleteColumn
        }).on('click', null, {colId}, deleteColumn)).append($('<span>', {
            id: 'button-col-add-' + colId,
            'data-col-id': colId,
            class: 'dashicons dashicons-plus action-button-add pointer',
            title: translations.addColumnAfterThisOne,
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

        if (!!selectedColId || selectedColId === 0) {
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
        tableRow.append($('<th>', {
            class: 'table-row-actions-cell sortable-row',
        }).append($('<span>', {
            class: 'dashicons dashicons-editor-expand drag-row',
            title: translations.dragAndDropRow
        })).append($('<span>', {
            id: 'button-row-delete-' + rowIdString,
            class: 'dashicons dashicons-minus action-button-delete pointer',
            title: translations.deleteRow
        }).on('click', null, {rowId: rowIdString}, deleteRow)).append($('<span>', {
            id: 'button-row-add-' + rowIdString,
            class: 'dashicons dashicons-plus action-button-add pointer',
            title: translations.addRowAfterThisOne
        }).on('click', null, {rowId: rowIdString}, openAddRowPopover)));

        // Add the header row cell (hidden if column header or none selected)
        lastCellId++;
        const headerType = $('#header-type').val();
        tableRow.append($('<td>', {
            id: 'cell-' + lastCellId,
            class: 'table-content-cell-html table-content-header-row ',
            style: ['ROW_HEADER', 'BOTH'].includes(headerType) ? '' : 'display: none',
            'data-col-id': 0,
            'data-cell-type': type,
        }).append($('<input>', {
            type: 'text',
            maxLength: 255,
            class: 'table-header-row-cell-content'
        })));

        // Add cells to complete the row
        $('.table-header-cell').slice(1).each((index, element) => {
            tableRow.append(makeCell(type, $(element).data('col-id')));
        });

        rowIdInput.val(rowId);
        initDragAndDropRow();
        updateHeaderStyle();
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
                        title: translations.selectImage
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
                        title: translations.addAffliateLink
                    }).on('click', null, {cellId: lastCellId}, openEditAffiliationLinkModal))
                    .append($('<div>', {
                        id: 'cell-content-link-list-' + lastCellId
                    }));
            default:
                return $('<td>', {
                    class: 'table-content-cell-unknown',
                    'data-col-id': colId
                }).append(translations.unknownType);
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
                title: translations.selectOrUploadImage,
                button: {
                    text: translations.validate
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
                        title: translations.removeImage,
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

            let affilitateLinkValue = !!currentAffiliateLinkId ? JSON.parse($('#cell-content-' + currentCellId).val())
                .find(affilitateLinkValue => !!affilitateLinkValue && affilitateLinkValue.id === currentAffiliateLinkId) : null;

            if (!affilitateLinkValue) {
                $('#webshop-select option:first').prop('selected', true);
            }

            initAffiliateLinkInputsModal(affilitateLinkValue);

            const cancel = () => function () {
                $(this).dialog('close');
            }

            let buttons;

            if (hasNoWebshop) {
             buttons = {
                 [translations.close]: cancel()
             };
            } else {
                buttons = !!affilitateLinkValue ? {
                    [translations.edit]: updateAffiliateLink,
                    [translations.delete]: removeAffiliateLink,
                    [translations.cancel]: cancel()
                } : {
                    [translations.add]: addAffiliateLink,
                    [translations.cancel]: cancel()
                };
            }

            $('#edit-affiliation-link-modal').dialog({
                resizable: true,
                minWidth: 410,
                title: !!affilitateLinkValue ? translations.editAffiliationLink : translations.createAffiliationLink,
                modal: true,
                buttons,
            });

            recalculateLink({
                data: {
                    url: currentAffiliationUrl,
                    parametersSelector: '.affiliation-parameter-input',
                    linkOverviewSelector: '#affiliation-link-overview'
                }
            });
        }
    }

    // Open modal wich edit headerOptions
    function openEditHeaderOptionsModal() {
        initHeaderOptionsModalParameters();

        $('#edit-header-options-modal').dialog({
            resizable: true,
            minWidth: 450,
            minHeight: 400,
            title: translations.editHeaderOptions,
            modal: true,
            buttons: {
                [translations.cancel]: function () {
                    $(this).dialog('close');
                },
                [translations.edit]: function () {
                    $('#header-options').val(JSON.stringify({
                        'column-background': $('#header-column-background').val(),
                        'column-color': $('#header-column-color').val(),
                        'column-font-weight': $('#header-column-font-weight').val(),
                        'column-font-size': $('#header-column-font-size').val(),
                        'row-background': $('#header-row-background').val(),
                        'row-color': $('#header-row-color').val(),
                        'row-font-weight': $('#header-row-font-weight').val(),
                        'row-font-size': $('#header-row-font-size').val(),
                    }));

                    updateHeaderStyle();
                    $(this).dialog('close');
                }
            },
        });
    }

    // Init header options modal parameter fields
    function initHeaderOptionsModalParameters() {
        Object.entries(JSON.parse($('#header-options').val()))
            .filter(([key,]) => !!key)
            .forEach(([key, value]) => {
                    if (key.endsWith('font-size')) {
                        $('#header-' + key + ' option[value="' + value + '"]').prop("selected", true);
                    } else {
                        const input = $('#header-' + key);
                        const stringValue = !!value ? value.toString() : '';

                        input.val(stringValue);
                        if (!key.endsWith('font-weight')) {
                            input.next().children().css('background-color', stringValue);
                        }
                    }
                }
            );
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
            title: translations.editAffiliateLink,
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
        if (!canUsePremiumCode) {
            return;
        }

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
        if (!canUsePremiumCode) {
            return;
        }

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
    function addRecalculationLinkEvents() {
        $('.affiliation-parameter-input').on('change keyup paste', event => {
            recalculateLink({
                data: {
                    url: currentAffiliationUrl,
                    parametersSelector: '.affiliation-parameter-input',
                    linkOverviewSelector: '#affiliation-link-overview'
                }
            });
        });

        recalculateLink({
            data: {
                url: currentAffiliationUrl,
                parametersSelector: '.affiliation-parameter-input',
                linkOverviewSelector: '#affiliation-link-overview'
            }
        });
    }

    // Clear and add preferences / selected values in the edit affiliation links modal depending on the selected webshop
    function initAffiliateLinkInputsModal(affilitateLinkValue) {
        if (hasNoWebshop) {
            return;
        }

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

        addRecalculationLinkEvents();
    }


    // Update editAffiliationLinkModalInputs (preferences if new, else filled values)
    function updateAffiateInputsModal(linkText, background, color) {
        $('#link-text-input').val(linkText);
        $('#link-background-color').val(background).trigger('paste');
        $('#link-text-color').val(color).trigger('paste');
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
});