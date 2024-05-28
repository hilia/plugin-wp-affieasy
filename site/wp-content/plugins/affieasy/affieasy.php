<?php
/*
 * Plugin Name: AffiEasy
 * Description: Plugin to easily and quickly generate responsive tables and manage affiliate links.
 * Version: 1.1.8
 * Text Domain: affieasy
 * Author: Affieasy Team
 * Author URI: https://www.affieasy.com/
 * License: GPLv2 or later
 */

use affieasy\AFES_AffiliationTableAdmin;
use affieasy\AFES_DbManager;
use affieasy\AFES_Constants;

if (!defined('ABSPATH')) {
    exit;
}

require_once 'classes/class-afes-affiliation-table-admin.php';
$plugin_instance = new AFES_AffiliationTableAdmin();

register_activation_hook(__FILE__, array($plugin_instance, 'initialize_affieasy_plugin'));

function after_plugins_loaded()
{
    load_plugin_textdomain('affieasy', FALSE, basename(dirname(__FILE__)) . '/languages/');

    // $plugin_version ="1.0.5";
    $plugin_version =AFES_Constants::PLUGIN_VERSION; // Version sans Fremius FreeWare
    if ($plugin_version !== get_option(AFES_Constants::AFFIEASY_PLUGIN_VERSION)) {
        AFES_AffiliationTableAdmin::initialize_affieasy_plugin();
        AFES_AffiliationTableAdmin::update_affieasy_plugin();
        update_option(AFES_Constants::AFFIEASY_PLUGIN_VERSION, $plugin_version);
    }
}

add_action('plugins_loaded', 'after_plugins_loaded');
