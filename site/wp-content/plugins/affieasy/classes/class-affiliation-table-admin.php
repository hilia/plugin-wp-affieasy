<?php

require_once 'class-webshop.php';
require_once 'class-table.php';
require_once 'class-db-manager.php';
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

        wp_enqueue_style('dashicons');
        wp_enqueue_style(
            'rendering-style',
            plugins_url('/affieasy/css/rendering.css'),
            array(),
            time());
    }

    public function initialize()
    {
        if (!$this->dbManager->table_exists(Constants::TABLE_WEBSHOP)) {
            $this->dbManager->create_table_webshop();
        }

        if (!$this->dbManager->table_exists(Constants::TABLE_TABLE)) {
            $this->dbManager->create_table_table();
        }
    }

    public function rollback()
    {
        $staticDbManager = DbManager::get_instance();
        $staticDbManager->drop_table(Constants::TABLE_WEBSHOP);
        $staticDbManager->drop_table(Constants::TABLE_TABLE);
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
            array($this, 'display_webshop_list')
        );
    }

    public function display_table_views()
    {
        if (is_admin() && current_user_can('manage_options')) {
            switch ($_GET['action']) {
                case 'edit-table':
                    include(dirname(__DIR__) . '/views/admin/edit-table.php');
                    break;
                default:
                    include(dirname(__DIR__) . '/views/admin/list-table.php');
                    break;
            }
        }
    }

    public function display_webshop_list()
    {
        if (is_admin() && current_user_can('manage_options')) {
            switch ($_GET['action']) {
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