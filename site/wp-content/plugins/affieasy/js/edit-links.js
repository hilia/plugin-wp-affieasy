jQuery(($) => {

    let url = '';

    $('#add-new-link').on('click', () => {
        const cancel = () => function () {
            $(this).dialog('close');
        }

        let buttons;

        buttons = {
            [translations.add]: createLink,
            [translations.cancel] : cancel()
        }

        $('#edit-link-modal').dialog({
            resizable: true,
            minWidth: 600,
            title: translations.addNewLink,
            modal: true,
            buttons
        });

        $('#label').val("");
        updateParameterInputs();
    });

    $('#webshopId').on('change', () => {
        updateParameterInputs();
    });

    function updateParameterInputs() {
        $('.link-parameter-row').remove();

        let selectedWebshop = $("#webshopId option:selected");
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

    function createLink() {
        const parameters = {};

        $('.link-parameter-input').each((colIndex, input) => {
            const jqueryInput = $(input);
            parameters[jqueryInput.data('parameter')] = !!jqueryInput.val() ? jqueryInput.val() : jqueryInput.data('parameter');
        });


        $('#parameters').val(JSON.stringify(parameters));
        $('#urlParam').val($('#p-overview').text());

        $('#form').trigger('submit');
    }
});
