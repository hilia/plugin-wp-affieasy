jQuery(($) => {

    let url = '';

    // Add openEditModal on each delete link
    $('.update-link').each(((index, element) => {
        const jqueryElement = $(element);
        jqueryElement.on('click', null, {
            id: $(element).data('id'),
            webshopId: $(element).data('webshopId'),
            label: $(element).data('label'),
            parameters: $(element).data('parameters'),
            url: $(element).data('url'),
            noFollow: $(element).data('noFollow')
        }, openEditModal);
    }));

    // Add openDeleteModal on each delete link
    $('.delete-link').each(((index, element) => {
        const jqueryElement = $(element);
        jqueryElement.on('click', null, {id: $(element).data('id')}, openDeleteModal);
    }));

    $('#add-new-link').on('click', () => {
        openEditModal();
    });

    $('#webshopIdParam').on('change', () => {
        updateParameterInputs();
    });

    function openEditModal(event) {
        const isEdition = !!event;

        $('#edit-link-modal').dialog({
            resizable: true,
            minWidth: 600,
            title: isEdition ? translations.editLink : translations.addNewLink,
            modal: true,
            buttons : {
                [isEdition ? translations.edit : translations.add]: editLink,
                [translations.cancel] : function () {
                    $(this).dialog('close');
                }
            }
        });

        if (isEdition) {
            const data = event.data;

            $('#idParam').val(data.id);
            $('#webshopIdParam').val(data.webshopId);
            $('#labelParam').val(data.label);
            $('#noFollowParam').prop('checked', data.noFollow === 1);

            updateParameterInputs();

            const parameters = JSON.parse(data.parameters.replaceAll("'", '"'));
            Object.keys(parameters).forEach(key => {
                $(`[data-parameter="${key}"]`).val(parameters[key]);
            });

            $('#p-overview').text(data.url);
        } else {
            $('#idParam').val('');
            $("#webshopIdParam option:first").attr('selected','selected').trigger("change");
            $('#labelParam').val('');
            $('#noFollowParam').prop('checked', true);

            updateParameterInputs();
        }
    }

    // Add inputs depending on webshop parameters
    function updateParameterInputs() {
        $('.link-parameter-row').remove();

        let selectedWebshop = $("#webshopIdParam option:selected");
        if (selectedWebshop) {
            url = selectedWebshop.data('url');

            selectedWebshop.data('parameters')
                .split('|||')
                .reverse()
                .forEach(parameter => $('#no-follow-row').after($('<tr>', {
                    class: 'link-parameter-row',
                })
                    .append($('<th>', {
                        scope: 'row'
                    }).append(`<label>${parameter}</label>`))
                    .append($('<td>').append($('<input>', {
                        type: 'text',
                        class: 'link-parameter-input width-100',
                        maxLength: 255,
                        'data-parameter': parameter
                    }).on('change keyup paste', null, {
                        url,
                        parametersSelector: '.link-parameter-input',
                        linkOverviewSelector: '#p-overview'}, recalculateLink)))));

            $('#p-overview').text(url);
        }
    }

    function editLink() {
        const parameters = {};

        $('.link-parameter-input').each((colIndex, input) => {
            const jqueryInput = $(input);
            parameters[jqueryInput.data('parameter')] = !!jqueryInput.val() ? jqueryInput.val() : jqueryInput.data('parameter');
        });

        $('#parametersParam').val(JSON.stringify(parameters));
        $('#urlParam').val($('#p-overview').text());

        $('#form').trigger('submit');
    }

    function openDeleteModal(event) {
        if (!!event && !!event.data && !isNaN(event.data.id)) {
            $('#dialog-confirm-delete').dialog({
                resizable: false,
                width: 350,
                modal: true,
                buttons: {
                    [translations.yes]: function () {
                        $('#actionType').val('deletion');
                        $('#idParam').val(event.data.id);
                        $('#form').trigger('submit');
                    },
                    [translations.no]: function () {
                        $(this).dialog('close');
                    }
                }
            });
        }
    }
});
