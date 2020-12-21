<?php
/*
 * Plugin Name: AffiEasy
 * Description: Plugin allowing to generate tables and manage affiliate links
 * Version: 0.3
*/

require_once 'classes/class-affiliation-table-admin.php';

$plugin_instance = new AffiliationTableAdmin();
register_activation_hook( __FILE__, array($plugin_instance, 'initialize') );