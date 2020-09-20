<?php

require_once 'class-table.php';
require_once 'class-advertising-agency.php';
require_once 'class-db-manager.php';
require_once dirname(__DIR__) . '/constants.php';

class AffiliationTableAdmin
{
    private $dbManager;

    private $advertisingAgencies;

    function __construct()
    {
        $this->dbManager = new DbManager();

        $this->advertisingAgencies = array();
        array_push($this->advertisingAgencies, new AdvertisingAgency('AWIN', 'Awin', null));
        array_push($this->advertisingAgencies, new AdvertisingAgency('EFFILIATION', 'Effiliation', null));
        array_push($this->advertisingAgencies, new AdvertisingAgency('AFFILAE', 'Affilae', null));

        add_action('admin_menu', array($this, 'add_menu_page_affiliation_table'));

        add_shortcode(Constants::TABLE_TAG, array($this, 'affiliation_table_content_callback'));
    }

    public function initialize()
    {
        if (!$this->dbManager->table_exists(Constants::ADVERTISING_AGENCY_TABLE)) {
            $this->dbManager->create_advertising_agency_table($this->advertisingAgencies);
        }

        if (!$this->dbManager->table_exists(Constants::TABLE_TABLE)) {
            $this->dbManager->create_table_table();
        }
    }

    public function add_menu_page_affiliation_table()
    {
        add_menu_page(
            'Affiliation',
            'Affiliation',
            'manage_options',
            'affiliationTableAdmin',
            array($this, 'select_page_to_show'),
            'dashicons-editor-table',
            20
        );
    }

    public function select_page_to_show()
    {
        switch ($_GET['action']) {
            case 'edit-advertising-agencies':
                include(dirname(__DIR__) . '/pages/edit-advertising-agencies.php');
                break;
            case 'edit-table':
                include(dirname(__DIR__) . '/pages/edit-table.php');
                break;
            default:
                include(dirname(__DIR__) . '/pages/main.php');
                break;
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
                <tbody>
                <?php for ($i = $isWithHeader ? 1 : 0; $i < count($tableContent); $i++) { ?>
                    <tr>
                        <?php $row = $tableContent[$i];
                        for ($j = 0; $j < count($row); $j++) { ?>
                            <td><?php echo $row[$j]->value; ?></td>
                        <?php } ?>
                    </tr>
                <?php } ?>
                </tbody>
            <?php } ?>
        </table>
        <?php
    }
}