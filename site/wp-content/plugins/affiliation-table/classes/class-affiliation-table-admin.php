<?php

require_once 'class-advertising-agency.php';

class AffiliationTableAdmin
{
    const ADVERTISING_AGENCY_TABLE = 'affiliation_table_advertising_agency';

    private $advertisingAgencies = array();

    private $db;

    function __construct()
    {
        global $wpdb;

        $this->db = $wpdb;

        add_action('admin_menu', array($this, 'add_menu_page_affiliation_table'));

        array_push($this->advertisingAgencies, new AdvertisingAgency('AWIN', 'Awin'));
        array_push($this->advertisingAgencies, new AdvertisingAgency('EFFILIATION', 'Effiliation'));
        array_push($this->advertisingAgencies, new AdvertisingAgency('AFFILAE', 'Affilae'));
    }

    public function initialize()
    {
        if ($this->db->get_var("SHOW TABLES LIKE '" . self::ADVERTISING_AGENCY_TABLE . "'") == '') {
            $this->create_advertising_agency_table();
        }
    }

    public function add_menu_page_affiliation_table()
    {
        add_menu_page(
            'Affiliation',
            'Affiliation',
            'manage_options',
            'views/main.php',
            '',
            'dashicons-editor-table',
            20
        );
    }

    private function create_advertising_agency_table()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sql = "
			CREATE TABLE " . self::ADVERTISING_AGENCY_TABLE . " (
				name VARCHAR(255) NOT NULL UNIQUE,
				label VARCHAR(255) NOT NULL,
				value VARCHAR(255)
			);
		";

        dbDelta($sql);

        foreach ($this->advertisingAgencies as $advertisingAgency) {
            $sql = "
                INSERT INTO " . self::ADVERTISING_AGENCY_TABLE . " (name, label) 
                VALUES ('" . $advertisingAgency->getName() . "', '" . $advertisingAgency->getLabel() . "')
            ";

            dbDelta($sql);
        }
    }
}