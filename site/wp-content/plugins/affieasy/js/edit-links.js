jQuery(($) => {

    let url = '';

    // Remove wordpress search event and add a custom new one
    $('#search-submit')
        .removeAttr("type")
        .attr("type", "button")
        .off()
        .on('click', () => search());

    $('#affieasy-search-input').on('keypress', (event) => {
        if (event.key === 'Enter') {
            search();
        }
    });

    // Add openEditModal on each delete link
    $('.update-link').each(((index, element) => {
        const jqueryElement = $(element);
        jqueryElement.on('click', null, {
            id: $(element).data('id'),
            webshopId: $(element).data('webshopId'),
            label: $(element).data('label'),
            category: $(element).data('category'),
            parameters: $(element).data('parameters'),
            url: $(element).data('url'),
            noFollow: $(element).data('noFollow'),
            openInNewTab: $(element).data('openInNewTab'),
        }, openEditModal);
    }));

    $('.copy-to-clipboard').each(((index, element) => {
        const jqueryElement = $(element);

        jqueryElement.on('click', null, () => {
            navigator.clipboard.writeText($(element).data('value'));
            jqueryElement.attr('title', $(element).data('type') === 'tag' ?
                translations.tagCopied :
                translations.shortUrlCopied
            );
        });

        jqueryElement.on('mouseout', null, () => {
            jqueryElement.attr('title', translations.copyToClipboard);
        });
    }));

    // Add openDeleteModal on each delete link
    /*
    $('.delete-link').each(((index, element) => {
        const jqueryElement = $(element);
        jqueryElement.on('click', null, {id: $(element).data('id')}, openDeleteModal);
    }));
    */

    $('#add-new-link').on('click', () => {
        openEditModal();
    });

    $('#webshopIdParam').on('change', () => {
        updateParameterInputs();
    });

    function search() {
        const urlParams = new URLSearchParams(window.location.search);
        const searchValue = $('#affieasy-search-input').val();

        let isSearchFilled = false;
        const params = [];
        urlParams.forEach((value, key) => {
            params.push(`${key}=${key === 's' ? searchValue : value}`);
            isSearchFilled = isSearchFilled || key === 's';
        });

        if (!isSearchFilled) {
            params.push('s=' + searchValue);
        }

        window.location.href = location.protocol + '//' + location.host + location.pathname + "?" +  params.join('&');
    }

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

            // console.log(data);
            $('#idParam').val(data.id);
            $('#webshopIdParam').val(data.webshopId);
            $('#labelParam').val(data.label);
            $('#categoryParam').val(data.category);
            $('#noFollowParam').prop('checked', data.noFollow === 1);
            $('#openInNewTabParam').prop('checked', data.openInNewTab === 1);

            updateParameterInputs();

            const parameters = JSON.parse(data.parameters.replaceAll("'", '"'));
            Object.keys(parameters).forEach(key => {
                $(`[data-parameter="${key}"]`).val(parameters[key]);
            });
            
            recalculateLink({
               data: {
                   url: $("#webshopIdParam option:selected").data('url'),
                   parametersSelector: '.link-parameter-input',
                   linkOverviewSelector: '#p-overview'
               }
            });
        } else {
            $('#idParam').val('');
            $("#webshopIdParam option:first").attr('selected','selected').trigger("change");
            $('#labelParam').val('');
            $('#categoryParam').val('');
            $('#noFollowParam').prop('checked', true);
            $('#openInNewTabParam').prop('checked', true);

            updateParameterInputs();
        }
    }

    // Add inputs depending on webshop parameters
    function updateParameterInputs() {
        $('.link-parameter-row').remove();

        let selectedWebshop = $("#webshopIdParam option:selected");
        if (selectedWebshop) {
            url = selectedWebshop.data('url');
            
            // console.log(url);

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

            // console.log(url);

            $('#p-overview').text(url);
        }
    }

    function editLink() {
        const parameters = {};

        $('.link-parameter-input').each((colIndex, input) => {
            const jqueryInput = $(input);
            parameters[jqueryInput.data('parameter')] = !!jqueryInput.val() ? jqueryInput.val() : '';
        });

        $('#parametersParam').val(JSON.stringify(parameters));
        $('#urlParam').val($('#p-overview').text());

        $('#form').trigger('submit');
    }
    /*
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
    */
});
