<?php
/*
 * Plugin Name: AffiEasy
 * Description: Plugin to easily and quickly generate responsive tables and manage affiliate links.
 * Version: 0.9.21
 * Text Domain: affieasy
 * Author: Affieasy Team
 * Author URI: https://www.affieasy.com/
 * License: GPLv2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

if (function_exists('aff_fs')) {
    aff_fs()->set_basename(true, __FILE__);
} else {
    if (!function_exists('aff_fs')) {
        // Create a helper function for easy SDK access.
        function aff_fs()
        {
            global $aff_fs;

            if (!isset($aff_fs)) {
                // Include Freemius SDK.
                require_once dirname(__FILE__) . '/freemius/start.php';

                $aff_fs = fs_dynamic_init(array(
                    'id' => '7661',
                    'slug' => 'affieasy',
                    'premium_slug' => 'undefined',
                    'type' => 'plugin',
                    'public_key' => 'pk_193da67aa0422908d1f081bb7b34b',
                    'is_premium' => true,
                    'premium_suffix' => '',
                    // If your plugin is a serviceware, set this option to false.
                    'has_premium_version' => true,
                    'has_addons' => false,
                    'has_paid_plans' => true,
                    'menu' => array(
                        'slug' => 'affieasy-table',
                        'first-path' => 'admin.php?page=affieasy-table',
                        'support' => false,
                    ),
                    // Set the SDK to work in a sandbox mode (for development & testing).
                    // IMPORTANT: MAKE SURE TO REMOVE SECRET KEY BEFORE DEPLOYMENT.
                    'secret_key' => 'sk_sVtKIw:nh~bErBdXy%iQw9=}Nu)RT',
                ));
            }

            return $aff_fs;
        }

        // Init Freemius.
        aff_fs();
        // Signal that SDK was initiated.
        do_action('aff_fs_loaded');
    }

    require_once 'classes/class-affiliation-table-admin.php';
    $plugin_instance = new AffiliationTableAdmin();

    register_activation_hook(__FILE__, 'AffiliationTableAdmin::initialize_affieasy_plugin');

    if (!function_exists('after_plugins_loaded')) {
        function after_plugins_loaded()
        {
            load_plugin_textdomain('affieasy', FALSE, basename(dirname(__FILE__)) . '/languages/');
        }
    }

    add_action('plugins_loaded', 'after_plugins_loaded');

    if (!function_exists('aff_fs_uninstall_cleanup')) {
        function aff_fs_uninstall_cleanup() {
            if (!is_dir(ABSPATH . 'wp-content/plugins/affieasy') || !is_dir(ABSPATH . 'wp-content/plugins/affieasy-premium')) {
                $staticDbManager = DbManager::get_instance();
                $staticDbManager->drop_table(Constants::TABLE_WEBSHOP);
                $staticDbManager->drop_table(Constants::TABLE_TABLE);
            }
        }
    }

    aff_fs()->add_action('after_uninstall', 'aff_fs_uninstall_cleanup');
}
