<?php
/*
 * Plugin Name: AffiEasy
 * Description: Plugin to easily and quickly generate responsive tables and manage affiliate links.
 * Version: 0.9.13
 * Text Domain: affieasy
*/

require_once 'classes/class-affiliation-table-admin.php';

$plugin_instance = new AffiliationTableAdmin();
register_activation_hook( __FILE__, array($plugin_instance, 'initialize') );
register_uninstall_hook( __FILE__, array('AffiliationTableAdmin', 'rollback') );


function affieasy_plugin_load_text_domain()
{
    load_plugin_textdomain('affieasy', FALSE, basename( dirname( __FILE__ ) ) . '/languages/');
}

add_action('plugins_loaded', 'affieasy_plugin_load_text_domain');


