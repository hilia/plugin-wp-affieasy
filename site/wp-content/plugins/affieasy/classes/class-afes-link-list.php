<?php

namespace affieasy;

use WP_List_Table;

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class AFES_LinkList extends WP_List_Table
{
    private $dbManager;

    function __construct()
    {
        parent::__construct([
            'singular' => 'Link',
            'plural' => 'Links',
            'ajax' => false,
        ]);

        $this->dbManager = new AFES_DbManager();
    }

    public function no_items()
    {
        _e('No Link found.', 'affieasy');
    }

    function get_columns()
    {
        return [
            'tag' => esc_html__('Tag', 'affieasy'),
            'webshop' => esc_html__('Webshop', 'affieasy'),
            'label'  => esc_html__('Link label', 'affieasy'),
            'url'  => esc_html__('Url', 'affieasy'),
        ];
    }

    function column_default($item, $column_name)
    {
        return stripslashes($item[$column_name]);
    }

    public function prepare_items()
    {
        $per_page = AFES_Constants::ITEMS_PER_PAGE;
        $total_items = $this->dbManager->get_table_count(AFES_Constants::TABLE_LINK);
        $data = $this->dbManager->get_link_page($this->get_pagenum(), $per_page);

        $this->items = $data;
        $this->_column_headers = array($this->get_columns(), array(), array());
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }
}