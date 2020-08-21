<?php

class DbManager
{
    private $db;

    function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function table_exists($tableName) {
        return $this->db->get_var("SHOW TABLES LIKE '" . $tableName . "'") != '';
    }

    public function create_advertising_agency_table($advertisingAgencies)
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sql = "
			CREATE TABLE " . Constants::ADVERTISING_AGENCY_TABLE . " (
				name VARCHAR(255) NOT NULL UNIQUE,
				label VARCHAR(255) NOT NULL,
				value VARCHAR(255)
			);
		";

        dbDelta($sql);

        foreach ($advertisingAgencies as $advertisingAgency) {
            $sql = "
                INSERT INTO " . Constants::ADVERTISING_AGENCY_TABLE . " (name, label) 
                VALUES ('" . $advertisingAgency->getName() . "', '" . $advertisingAgency->getLabel() . "')
            ";

            dbDelta($sql);
        }
    }

    public function get_advertising_agencies()
    {
        $query = "SELECT * FROM " . Constants::ADVERTISING_AGENCY_TABLE;

        return array_map(function ($advertisingAgency) {
            return new AdvertisingAgency($advertisingAgency["name"], $advertisingAgency["label"], $advertisingAgency["value"]);
        }, $this->db->get_results($query, ARRAY_A));
    }

    public function save_advertising_agency_ids($advertisingAgencies)
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        foreach ($advertisingAgencies as $advertisingAgency) {
            $sql = "
                UPDATE " . Constants::ADVERTISING_AGENCY_TABLE . " 
                SET value = '" . $advertisingAgency->getValue() . "'
                WHERE name = '" . $advertisingAgency->getName() . "'
            ";

            dbDelta($sql);
        }
    }
}