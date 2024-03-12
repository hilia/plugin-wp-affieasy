<?php

namespace affieasy;

require_once 'class-afes-webshop.php';
require_once 'class-afes-table.php';
require_once 'class-afes-link.php';
require_once 'class-afes-db-manager.php';
require_once 'class-afes-utils.php';
require_once 'class-afes-generation-utils.php';
require_once dirname(__DIR__) . '/afes-constants.php';

class AFES_AffiliationTableAdmin
{
    private $dbManager;

    function __construct()
    {
        $this->dbManager = new AFES_DbManager();

        add_action('admin_menu', array($this, 'add_menus_page_affiliation_table'));

        add_shortcode(AFES_Constants::TABLE_TAG, array($this, 'affieasy_table_content_callback'));
        add_shortcode(AFES_Constants::LINK_TAG, array($this, 'affieasy_link_content_callback'));

        add_action( 'template_redirect', array( __CLASS__, 'link_redirect' ) );

        add_action('wp_enqueue_scripts', function () {
            wp_enqueue_style('dashicons');
            wp_enqueue_style(
                'rendering-style',
                plugins_url('/' . AFES_Utils::get_plugin_name() . '/css/rendering.css'),
                array(),
                time());
        });
    }

    public static function initialize_affieasy_plugin()
    {
        $staticDbManager = AFES_DbManager::get_instance();
        if (!$staticDbManager->table_exists(TABLE_WEBSHOP)) {
            $staticDbManager->create_table_webshop();
        }

        if (!$staticDbManager->table_exists(TABLE_TABLE)) {
            $staticDbManager->create_table_table();
        }

        if (!$staticDbManager->table_exists(TABLE_LINK)) {
            $staticDbManager->create_table_link();
        }

    }

    public static function update_affieasy_plugin()
    {
        $staticDbManager = AFES_DbManager::get_instance();
        // W-prog Update le 22/11/2023 : creation champ encodeUrl 
        $staticDbManager->update_table_webshop_encodeUrl();

    }

    public function add_menus_page_affiliation_table()
    {
        add_menu_page(
            'AffiEasy',
            'AffiEasy',
            'manage_options',
            'affieasy-table',
            array($this, 'display_table_views'),
            'dashicons-editor-table',
            20
        );

        add_submenu_page(
            'affieasy-table',
            esc_html__('Tables', 'affieasy'),
            esc_html__('Tables', 'affieasy'),
            'manage_options',
            'affieasy-table',
            array($this, 'display_table_views')
        );

        add_submenu_page(
            'affieasy-table',
            esc_html__('Affiliate links', 'affieasy'),
            esc_html__('Affiliate links', 'affieasy'),
            'manage_options',
            'affieasy-link',
            array($this, 'display_links_view')
        );

        add_submenu_page(
            'affieasy-table',
            esc_html__('Webshops', 'affieasy'),
            esc_html__('Webshops', 'affieasy'),
            'manage_options',
            'affieasy-webshop',
            array($this, 'display_webshop_views')
        );
    }

    public function display_table_views()
    {
        if (is_admin() && current_user_can('manage_options')) {
            $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : null;

            switch ($action) {
                case 'edit-table':
                    include(dirname(__DIR__) . '/views/admin/edit-table.php');
                    break;
                default:
                    include(dirname(__DIR__) . '/views/admin/list-table.php');
                    break;
            }
        }
    }

    public function display_links_view()
    {
        if (is_admin() && current_user_can('manage_options')) {
            include(dirname(__DIR__) . '/views/admin/edit-links.php');
        }
    }

    public function display_webshop_views()
    {
        if (is_admin() && current_user_can('manage_options')) {
            $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : null;

            switch ($action) {
                case 'edit-webshop':
                    include(dirname(__DIR__) . '/views/admin/edit-webshop.php');
                    break;
                default:
                    include(dirname(__DIR__) . '/views/admin/list-webshop.php');
                    break;
            }
        }
    }

    public function affieasy_table_content_callback($atts)
    {
        ob_start();

        $table = $this->dbManager->get_table_by_id(intval($atts['id']));
        if ($table->getId() == null) { ?>
            <h6><?php esc_html_e('Table not found.', 'affieasy'); ?></h6>
        <?php } else {
            AFES_GenerationUtils::generate_table($table);
        }

        return ob_get_clean();
    }

    public function affieasy_link_content_callback($atts)
    {
        ob_start();

        $link = $this->dbManager->get_link_by_id(intval($atts['id']));
        if (isset($link)) {
            AFES_GenerationUtils::generate_link($link);
        }

        return ob_get_clean();
    }

    public static function link_redirect()
    {
        $linkId = isset($_GET['affieasy-link']) ? $_GET['affieasy-link'] : null;

        if ($linkId !== null && is_numeric($linkId)) {
            $link = AFES_DbManager::get_instance()->get_link_by_id($linkId);

            if ($link->getId() === null) {
                global $wp_query;
                $wp_query->set_404();
                status_header( 404 );
                get_template_part( 404 );
            } else {
                wp_redirect( $link->getUrl(), intval(301));
            }

            exit();
        }
    }
}