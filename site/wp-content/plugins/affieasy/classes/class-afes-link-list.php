<?php

namespace affieasy;

use WP_List_Table;

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class AFES_LinkList extends WP_List_Table
{
    private $dbManager;

    private $baseUrl;

    function __construct()
    {
        parent::__construct([
            'singular' => 'Link',
            'plural' => 'Links',
            'ajax' => false,
        ]);

        $this->dbManager = new AFES_DbManager();
        $this->baseUrl =  AFES_Utils::get_base_url();
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
            'label' => esc_html__('Link label', 'affieasy'),
            'category' => esc_html__('Category', 'affieasy'),
            'shortUrl' => esc_html__('Short url', 'affieasy'),
            'url'  => esc_html__('Url', 'affieasy'),
        ];
    }

    function column_tag($item)
    {
        $tag = $item['tag'];

        $url = $item['url'];
        
        return sprintf('%1$s %2$s',
            '<span data-type="tag" data-value="' . $tag . '" class="dashicons dashicons-admin-links copy-to-clipboard" title="' . esc_html__('Copy to clipboard', 'affieasy') . '"></span>' . $tag,
            $this->row_actions(array(
                'edit' => sprintf('<a href="#" class="update-link" data-id="' . $item['id'] . '" data-webshop-id="' . $item['webshopId'] . '" data-label="' . $item['label'] . '" data-category="' . $item['category'] . '" data-parameters="' . str_replace('"', "'", $item['parameters']) . '" data-url="' .  $url . '" data-no-follow="' . $item['noFollow'] . '" data-open-in-new-tab="' . $item['openInNewTab'] . '">' . esc_html__('Edit', 'affieasy') . '</a>'),
                'delete' => sprintf('<a href="#" class="delete-link" data-id="' . $item['id'] . '">' . esc_html__('Delete', 'affieasy') . '</a>'),
            ))
        );
    }

    function column_shortUrl($item)
    {
        $shortUrl = $this->baseUrl . '?' . AFES_Constants::SHORT_LINK_SLUG . '=' . $item['id'];
        return '<span data-value="' . $shortUrl . '" class="dashicons dashicons-admin-links copy-to-clipboard" title="' . esc_html__('Copy to clipboard', 'affieasy') . '"></span>' . $shortUrl;
    }

    function column_url($item)
    {

        $url = $item['url'];
        
        // w-prog : pour un affichage correct de l'url encodée dans la liste.
        $parameters = $item['parameters'];
        $parameters = json_decode($parameters);
        $product_url="";
        foreach ($parameters as $clef => $valeur){
            if ($clef=="product_url"){
                $product_url=$valeur;
            }
        }
        $dbManager = new AFES_DbManager();
        $webshop = $dbManager->get_webshop_by_id($item['webshopId']);
        $encoderUrl = $webshop->getEncoderUrl();
        if ($encoderUrl=="1"){
            $url = str_replace($product_url, urlencode($product_url), $url );
        }
        // Fin w-prog
        return $url;
    }

    public function get_sortable_columns()
    {
        return array(
            'tag' => array('tag', false),
            'webshop' => array('webshop', false),
            'label' => array('label', false),
            'category' => array('category', false),
            'shortUrl' => array('shortUrl', false),
            'url' => array('url', false));
    }

    function column_default($item, $column_name)
    {
        return stripslashes($item[$column_name]);
    }

    public function prepare_items()
    {
        $per_page = AFES_Constants::ITEMS_PER_PAGE;

        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : null;
        $total_items = $search === null ?
            $this->dbManager->get_table_count(AFES_Constants::TABLE_LINK) :
            $this->dbManager->get_link_count($search);

        $data = $this->dbManager->get_link_page(
            $this->get_pagenum(),
            $per_page,
            isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : null,
            isset($_GET['order']) ? sanitize_key($_GET['order']) : null,
            $search
        );

        $this->items = $data;
        $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }
}