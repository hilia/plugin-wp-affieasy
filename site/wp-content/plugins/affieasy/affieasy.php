<?php
/*
 * Plugin Name: AffiEasy
 * Description: Plugin to easily and quickly generate responsive tables and manage affiliate links.
 * Version: 0.9.13
 * Text Domain: affieasy
*/

require_once 'classes/class-affiliation-table-admin.php';

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
                'premium_suffix'      => 'undefined',
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

$plugin_instance = new AffiliationTableAdmin();
register_activation_hook(__FILE__, array($plugin_instance, 'initialize'));
register_uninstall_hook(__FILE__, array('AffiliationTableAdmin', 'rollback'));

function affieasy_plugin_load_text_domain()
{
    load_plugin_textdomain('affieasy', FALSE, basename(dirname(__FILE__)) . '/languages/');
}

add_action('plugins_loaded', 'affieasy_plugin_load_text_domain');


