<?php

class AffiliationTableAdmin {

    function __construct() {
        add_action('admin_menu', array($this, 'add_menu_page_affiliation_table'));
    }

    function add_menu_page_affiliation_table() {
        add_menu_page(
            'Affiliation',
            'Affiliation',
            'manage_options',
            'views/main.php',
            '',
            'dashicons-editor-table',
            20
        );
    }

    public function initialize() {
    }
}