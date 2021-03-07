<?php

namespace affieasy;

use WP_List_Table;

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class AFES_TableList extends WP_List_Table
{
    private $dbManager;

    function __construct()
    {
        parent::__construct([
            'singular' => 'Table',
            'plural' => 'Tables',
            'ajax' => false,
        ]);

        $this->dbManager = new AFES_DbManager();
    }

    public function no_items()
    {
        _e('No table found.', 'affieasy');
    }

    function get_columns()
    {
        return [
            'id' => esc_html__('Id', 'affieasy'),
            'name' => esc_html__('Name', 'affieasy')
        ];
    }

    function column_id($item)
    {
        $id = $item['id'];

        return sprintf('%1$s %2$s',
            $item['id'],
            $this->row_actions(array(
                'edit' => sprintf('<a href="admin.php?page=affieasy-table&action=edit-table&id=' . $id . '">' . esc_html__('Edit', 'affieasy') . '</a>'),
                'delete' => sprintf('<a href="#" class="delete-link" data-id="' . $id . '">' . esc_html__('Delete', 'affieasy') . '</a>')
            ))
        );
    }

    function column_default($item, $column_name)
    {
        return stripslashes($item[$column_name]);
    }

    public function prepare_items()
    {
        $per_page = AFES_Constants::ITEMS_PER_PAGE;
        $total_items = $this->dbManager->get_table_count(AFES_Constants::TABLE_TABLE);
        $data = $this->dbManager->get_table_page($this->get_pagenum(), $per_page);

        $this->items = $data;
        $this->_column_headers = array($this->get_columns(), array(), array());
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }
}