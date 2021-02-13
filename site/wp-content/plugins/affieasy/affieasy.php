<?php
/*
 * Plugin Name: AffiEasy
 * Description: Plugin to easily and quickly generate responsive tables and manage affiliate links.
 * Version: 0.9.14
 * Text Domain: affieasy
*/

if ( ! function_exists( 'aff_fs' ) ) {
    // Create a helper function for easy SDK access.
    function aff_fs() {
        global $aff_fs;

        if ( ! isset( $aff_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $aff_fs = fs_dynamic_init( array(
                'id'                  => '7661',
                'slug'                => 'affieasy',
                'premium_slug'        => 'undefined',
                'type'                => 'plugin',
                'public_key'          => 'pk_193da67aa0422908d1f081bb7b34b',
                'is_premium'          => true,
                'premium_suffix'      => '',
                // If your plugin is a serviceware, set this option to false.
                'has_premium_version' => true,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                'menu'                => array(
                    'slug'           => 'affieasy-table',
                    'first-path'     => 'admin.php?page=affieasy-table',
                    'support'        => false,
                ),
                // Set the SDK to work in a sandbox mode (for development & testing).
                // IMPORTANT: MAKE SURE TO REMOVE SECRET KEY BEFORE DEPLOYMENT.
                'secret_key'          => 'sk_sVtKIw:nh~bErBdXy%iQw9=}Nu)RT',
            ) );
        }

        return $aff_fs;
    }

    // Init Freemius.
    aff_fs();
    // Signal that SDK was initiated.
    do_action( 'aff_fs_loaded' );
}

require_once 'classes/class-affiliation-table-admin.php';
$plugin_instance = new AffiliationTableAdmin();

register_activation_hook(__FILE__, 'AffiliationTableAdmin::initialize');
register_uninstall_hook(__FILE__, array('AffiliationTableAdmin', 'rollback'));

if (!function_exists('after_plugins_loaded'))
{
    function after_plugins_loaded()
    {
        load_plugin_textdomain('affieasy', FALSE, basename(dirname(__FILE__)) . '/languages/');
    }
}

if (!function_exists('after_plugin_activated'))
{
    function after_plugin_activated()
    {
        $currentPluginName = strpos(dirname(__FILE__), '-premium') === false ? 'affieasy' : 'affieasy-premium';
        $directoryToRemove = dirname(__DIR__) . '/' . ($currentPluginName === 'affieasy' ? 'affieasy-premium' : 'affieasy');

        require_once 'classes/class-utils.php';
        if (is_dir($directoryToRemove)) {
            Utils::remove_directory($directoryToRemove);
        }
    }
}

if (!function_exists('after_upgrader_process_complete'))
{
    function after_upgrader_process_complete()
    {
        if (is_dir(dirname(__DIR__) . '/affieasy') && is_dir(dirname(__DIR__) . '/affieasy-premium')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
            deactivate_plugins(array('affieasy/affieasy.php', 'affieasy-premium/affieasy.php'));
        }
    }
}

add_action('plugins_loaded', 'after_plugins_loaded');
add_action('activate_plugin', 'after_plugin_activated');
add_action('upgrader_process_complete', 'after_upgrader_process_complete');