<?php
if (isset($_REQUEST['s']) && strlen($_REQUEST['s'])) {
    echo '<span class="subtitle">';
    printf(
        __('Search results for: %s'),
        '<strong>' . esc_html($_REQUEST['s']) . '</strong>'
    );
    echo '</span>';
}
