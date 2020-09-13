jQuery(($) => {

    // Add openDeleteModal on each delete link
    $('.delete-link').each(((index, element) => {
        const jqueryElement = $(element);
        jqueryElement.on('click', null, {id: $(element).data('id')}, openDeleteModal);
    }));

    function openDeleteModal(event) {
        if (!!event && !!event.data && !isNaN(event.data.id)) {

            $('#dialog-confirm-delete').dialog({
                resizable: false,
                width: 350,
                modal: true,
                buttons: {
                    'Yes': function () {
                        window.location.href = 'admin.php?page=affiliationTableAdmin&action=delete-table&id=' + event.data.id;
                    },
                    'No': function () {
                        $(this).dialog('close');
                    }
                }
            });
        }
    }
});
