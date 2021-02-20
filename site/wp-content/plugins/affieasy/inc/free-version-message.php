<?php if (aff_fs()->is_not_paying()) { ?>
    <h4>
        <?php esc_html_e('You are using the free affieasy version. Get premium licence on', 'affieasy'); ?>
        <a href="https://www.affieasy.com">affieasy.com</a> <?php esc_html_e('or on the', 'affieasy'); ?>
        <a href="admin.php?page=affieasy-table-pricing"><?php esc_html_e('payment page', 'affieasy'); ?></a>
        <?php esc_html_e('to take full advantage of the features available!', 'affieasy'); ?>
    </h4>
<?php } ?>