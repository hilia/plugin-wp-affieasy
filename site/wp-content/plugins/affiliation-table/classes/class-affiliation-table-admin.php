<?php

require_once 'class-webshop.php';
require_once 'class-table.php';
require_once 'class-db-manager.php';
require_once dirname(__DIR__) . '/constants.php';

class AffiliationTableAdmin
{
    private $dbManager;

    function __construct()
    {
        $this->dbManager = new DbManager();

        add_action('admin_menu', array($this, 'add_menus_page_affiliation_table'));

        add_shortcode(Constants::TABLE_TAG, array($this, 'affiliation_table_content_callback'));
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

    public function add_menus_page_affiliation_table()
    {
        add_menu_page(
            'Affiliation',
            'Affiliation',
            'manage_options',
            'affiliation-table-table',
            array($this, 'display_table_pages'),
            'dashicons-editor-table',
            20
        );

        add_submenu_page(
            'affiliation-table-table',
            'Tables',
            'Tables',
            'manage_options',
            'affiliation-table-table',
            array($this, 'display_table_pages')
        );

        add_submenu_page(
            'affiliation-table-table',
            'Webshops',
            'Webshops',
            'manage_options',
            'affiliation-table-webshop',
            array($this, 'display_webshop_list')
        );
    }

    public function display_table_pages()
    {
        if (current_user_can('manage_options')) {
            switch ($_GET['action']) {
                case 'edit-table':
                    include(dirname(__DIR__) . '/pages/edit-table.php');
                    break;
                default:
                    include(dirname(__DIR__) . '/pages/list-table.php');
                    break;
            }
        }
    }

    public function display_webshop_list()
    {
        if (current_user_can('manage_options')) {
            switch ($_GET['action']) {
                case 'edit-webshop':
                    include(dirname(__DIR__) . '/pages/edit-webshop.php');
                    break;
                default:
                    include(dirname(__DIR__) . '/pages/list-webshop.php');
                    break;
            }
        }
    }

    public function affiliation_table_content_callback($atts)
    {
        ob_start();

        $table = $this->dbManager->get_table_by_id(intval($atts['id']));
        if ($table->getId() == null) { ?>
            <h6>Table not found.</h6>
        <?php } else {
            $this->generateTable($table);
        }

        return ob_get_clean();
    }

    function generateTable($table)
    {
        $isWithHeader = $table->isWithHeader();
        $tableContent = $table->getContent();
        $colNumber = count($tableContent[0]);

        ?>
        <table>
            <?php if ($isWithHeader) { ?>
                <thead>
                <tr>
                    <?php $header = $tableContent[0];
                    for ($i = 0; $i < $colNumber; $i++) { ?>
                        <th>
                            <?php echo $header[$i]->value; ?>
                        </th>
                    <?php } ?>
                </tr>
                </thead>
            <?php } ?>
            <tbody>
            <?php for ($i = $isWithHeader ? 1 : 0; $i < count($tableContent); $i++) { ?>
                <tr>
                    <?php $row = $tableContent[$i];
                    for ($j = 0; $j < count($row); $j++) {
                        $cellType = $row[$j]->type;
                        $cellValue = $row[$j]->value;
                        if (in_array($cellType, array(Constants::HTML, Constants::IMAGE))) { ?>
                            <td><?php echo $cellValue; ?></td>
                        <?php } else if ($cellType === Constants::AFFILIATION) {
                            $affiliateLinks = json_decode(str_replace("&quot;", '"', $cellValue));
                            ?>
                            <td>
                                <?php foreach ($affiliateLinks as $affiliateLink) { ?>
                                    <a href="<?php echo $affiliateLink->url; ?>" class="button button-primary">
                                        <span class="dashicons dashicons-cart cell-content-link-list-icon"></span>
                                        <span><?php echo $affiliateLink->linkText; ?></span>
                                    </a>
                                <?php } ?>
                            </td>
                        <?php }
                    } ?>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php
    }
}