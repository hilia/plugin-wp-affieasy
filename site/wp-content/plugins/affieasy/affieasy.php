<?php
/*
 * Plugin Name: AffiEasy
 * Description: Plugin allowing to generate responsive tables and manage affiliate links
 * Version: 0.9.9
*/

require_once 'classes/class-affiliation-table-admin.php';

$plugin_instance = new AffiliationTableAdmin();
register_activation_hook( __FILE__, array($plugin_instance, 'initialize') );
register_uninstall_hook( __FILE__, array('AffiliationTableAdmin', 'rollback') );