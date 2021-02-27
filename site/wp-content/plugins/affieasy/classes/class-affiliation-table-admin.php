<?php

require_once 'class-webshop.php';
require_once 'class-table.php';
require_once 'class-db-manager.php';
require_once 'class-utils.php';
require_once 'class-generation-utils.php';
require_once dirname(__DIR__) . '/constants.php';

class AffiliationTableAdmin
{
    private $dbManager;

    function __construct()
    {
        $this->dbManager = new DbManager();

        add_action('admin_menu', array($this, 'add_menus_page_affiliation_table'));

        add_shortcode(Constants::TABLE_TAG, array($this, 'affieasy_table_content_callback'));

        add_action('wp_enqueue_scripts', function () {
            wp_enqueue_style('dashicons');
            wp_enqueue_style(
                'rendering-style',
                plugins_url('/' . Utils::get_plugin_name() . '/css/rendering.css'),
                array(),
                time());
        });
    }

    public static function initialize_affieasy_plugin()
    {
        $staticDbManager = DbManager::get_instance();
        if (!$staticDbManager->table_exists(Constants::TABLE_WEBSHOP)) {
            $staticDbManager->create_table_webshop();
        }

        if (!$staticDbManager->table_exists(Constants::TABLE_TABLE)) {
            $staticDbManager->create_table_table();
        }
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
            __('Tables', 'affieasy'),
            __('Tables', 'affieasy'),
            'manage_options',
            'affieasy-table',
            array($this, 'display_table_views')
        );

        add_submenu_page(
            'affieasy-table',
            __('Webshops', 'affieasy'),
            __('Webshops', 'affieasy'),
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
            GenerationUtils::generate_table($table);
        }

        return ob_get_clean();
    }
}