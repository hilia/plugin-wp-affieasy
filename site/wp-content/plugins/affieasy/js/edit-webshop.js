jQuery(($) => {
    let isHelpOpened = false;

    //init color pickers
    $('#background-color-preference').minicolors({});
    $('#text-color-preference').minicolors({});

    $('#helper-title').on('click', () => {
        const helperContent = $('#helper-content');
        const helperIcon = $('#helper-icon');

        if (isHelpOpened) {
            helperContent.slideUp(400);

            helperIcon
                .removeClass('dashicons-arrow-up')
                .addClass('dashicons-arrow-down');

            isHelpOpened = false;
        } else {
            helperContent.slideDown(400);

            helperIcon
                .removeClass('dashicons-arrow-down')
                .addClass('dashicons-arrow-up');

            isHelpOpened = true;
        }
    });
});