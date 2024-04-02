<?php

namespace affieasy;

use WP_List_Table;

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class AFES_WebshopList extends WP_List_Table
{
    private $dbManager;

    function __construct()
    {
        parent::__construct([
            'singular' => 'Webshop',
            'plural' => 'Webshops',
            'ajax' => false,
        ]);

        $this->dbManager = new AFES_DbManager();
    }

    public function no_items()
    {
        _e('No webshop found.', 'affieasy');
    }

    function get_columns()
    {
        return [
            'id' => esc_html__('Id', 'affieasy'),
            'name' => esc_html__('Name', 'affieasy')
            /*,'encodeUrl' => esc_html__('EncodeUrl', 'affieasy')*/
        ];
    }

    function column_id($item)
    {
        $id = $item['id'];
        $nonce = wp_create_nonce( 'my-nonce' );
        $urlDelete = 'admin.php?page=affieasy-webshop&action=delete-webshop&id='.$id.'&_wpnonce='.$nonce;


        return sprintf('%1$s %2$s',
            $item['id'],
            $this->row_actions(array(
                'edit' => sprintf('<a href="admin.php?page=affieasy-webshop&action=edit-webshop&id=' . $id . '">' . esc_html__('Edit', 'affieasy') . '</a>'),
                'delete' => sprintf('<a href="'.$urlDelete.'" class="delete-webshop-confirm">' . esc_html__('Delete', 'affieasy') . '</a>')
                /*'delete' => sprintf('<a href="#" class="delete-link" data-id="' . $id . '">' . esc_html__('Delete', 'affieasy') . '</a>')*/
                
            ))
        );
    }

    public function get_sortable_columns()
    {
        return array(
            'id' => array('id', false),
            'name' => array('name', false));
    }

    function column_default($item, $column_name)
    {
        return stripslashes($item[$column_name]);
    }

    public function prepare_items()
    {
        $per_page = AFES_Constants::ITEMS_PER_PAGE;
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : null;
        $total_items = $this->dbManager->get_table_count(TABLE_WEBSHOP);
        // $data = $this->dbManager->get_webshop_page($this->get_pagenum(), $per_page);
        $data = $this->dbManager->get_webshop_page(
            $this->get_pagenum(),
            $per_page,
            isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : null,
            isset($_GET['order']) ? sanitize_key($_GET['order']) : null,
            $search
        );

        $this->items = $data;
        // $this->_column_headers = array($this->get_columns(), array(), array());
        $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }
}