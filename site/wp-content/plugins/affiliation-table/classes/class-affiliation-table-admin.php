<?php

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
    }

    public function initialize()
    {
        if (!$this->dbManager->table_exists(Constants::ADVERTISING_AGENCY_TABLE)) {
            $this->dbManager->create_advertising_agency_table($this->advertisingAgencies);
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
            default:
                include(dirname(__DIR__) . '/pages/main.php');
                break;
        }
    }
}