<?php

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class TableList extends WP_List_Table
{
    private $dbManager;

    function __construct()
    {
        parent::__construct([
            'singular' => 'Table',
            'plural' => 'Tables',
            'ajax' => false,
        ]);

        $this->dbManager = new DbManager();
    }

    public function no_items()
    {
        _e( 'No tables found.' );
    }

    function get_columns()
    {
        return [
            'cb' => '<input type="checkbox" />',
            'id' => 'Id',
            'name' => 'Name'
        ];
    }

    function column_id($item)
    {
        $actions = array(
            'edit' => sprintf('<a href="admin.php?page=affiliationTableAdmin&action=edit-table&id=' . $item['id'] . '">Edit</a>'),
        );

        return sprintf('%1$s %2$s',
            $item['id'],
            $this->row_actions($actions)
        );
    }

    function column_default($item, $column_name)
    {
        return stripslashes($item[$column_name]);
    }

    public function prepare_items()
    {
        $per_page = Constants::ITEMS_PER_PAGE;
        $total_items = $this->dbManager->get_tables_count();
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