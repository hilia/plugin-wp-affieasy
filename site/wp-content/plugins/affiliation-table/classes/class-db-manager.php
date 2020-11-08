<?php

class DbManager
{
    private $db;

    function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    /****************************** General functions ******************************/
    public function table_exists($tableName)
    {
        return !empty($this->db->get_var("SHOW TABLES LIKE '" . $tableName . "'"));
    }

    public function get_table_count($tableName)
    {
        return $this->db->get_var("SELECT COUNT(*) FROM " . $tableName);
    }

    /****************************** Webshop functions ******************************/

    public function create_table_webshop()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta(" CREATE TABLE " . Constants::TABLE_WEBSHOP . " (
			    id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
				name VARCHAR(255) NOT NULL,
				url TEXT NOT NULL
			);");
    }

    public function get_webshop_list()
    {
        return array_map(function($webshop) {
            return new Webshop(intval($webshop['id']), $webshop['name'], $webshop['url']);
        }, $this->db->get_results('SELECT * FROM ' . Constants::TABLE_WEBSHOP, ARRAY_A));
    }

    public function get_webshop_page($currentPage, $perPage)
    {
        $sql = $this->db->prepare(
            "SELECT * FROM " . Constants::TABLE_WEBSHOP . " ORDER BY id DESC LIMIT %d, %d",
            array((($currentPage - 1) * $perPage), $perPage));

        return $this->db->get_results($sql, ARRAY_A);
    }

    public function get_webshop_by_id($id)
    {
        $sql = $this->db->prepare("SELECT * FROM " . Constants::TABLE_WEBSHOP . " WHERE id=%d", array($id));
        $webshop = $this->db->get_row($sql);

        return new Webshop($webshop->id, $webshop->name, $webshop->url);
    }

    public function edit_webshop($webshop)
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $webshopId = $webshop->getId();
        $webshopName = $webshop->getName();
        $webshopUrl = $webshop->getUrl();

        if (empty($webshopId)) {
            $this->db->insert(Constants::TABLE_WEBSHOP, array(
                "name" => $webshopName,
                "url" => $webshopUrl));

            $webshopId = $this->db->insert_id;
        } else {
            $this->db->update(Constants::TABLE_WEBSHOP, array(
                "name" => $webshopName,
                "url" => $webshopUrl), array("id" => $webshopId));
        }

        return $this->get_webshop_by_id($webshopId);
    }

    public function delete_webshop($id)
    {
        $this->db->delete(Constants::TABLE_WEBSHOP, array('id' => $id));
    }

    /****************************** Table functions ******************************/
    public function create_table_table()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta("CREATE TABLE " . Constants::TABLE_TABLE . " (
			    id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
				name VARCHAR(255) NOT NULL,
				withHeader BOOLEAN NOT NULL DEFAULT true,
				headerOptions JSON NOT NULL,
				content JSON NOT NULL
			);");
    }

    public function get_table_page($currentPage, $perPage)
    {
        $sql = $this->db->prepare(
            "SELECT * FROM " . Constants::TABLE_TABLE . " ORDER BY id DESC LIMIT %d, %d",
            array((($currentPage - 1) * $perPage), $perPage));

        return $this->db->get_results($sql, ARRAY_A);
    }

    public function get_table_by_id($id)
    {
        $sql = $this->db->prepare("SELECT * FROM " . Constants::TABLE_TABLE . " WHERE id=%d", array($id));
        $table = $this->db->get_row($sql);

        $tableId = $table->id;

        $content = empty($tableId) ? null : array_map(function ($row) {
            return array_map(function ($cell) {
                return $cell;
            }, $row);
        }, json_decode($table->content));

        return new Table($tableId, $table->name, $table->withHeader, json_decode($table->headerOptions), $content);
    }

    public function edit_table($table)
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $tableId = $table->getId();
        $tableName = $table->getName();
        $isTableWithHeader = $table->isWithHeader();

        $headerOptions = str_replace('\\', '', $table->getHeaderOptions());

        $parsedArray = json_encode(array_map(function ($row) {
            return array_map(function ($cell) {
                return json_decode(
                    str_replace("\\", "",
                        str_replace('\\\\\\"', "&quot;",
                            str_replace('\\n', '&NewLine;', $cell))));
            }, $row);
        }, $table->getContent()));

        if (empty($tableId)) {
            $this->db->insert(Constants::TABLE_TABLE, array(
                "name" => $tableName,
                "withHeader" => $isTableWithHeader,
                "headerOptions" => $headerOptions,
                "content" => $parsedArray));

            $tableId = $this->db->insert_id;
        } else {
            $this->db->update(Constants::TABLE_TABLE, array(
                "name" => $tableName,
                "withHeader" => $isTableWithHeader,
                "headerOptions" => $headerOptions,
                "content" => $parsedArray), array("id" => $tableId));
        }

        return $this->get_table_by_id($tableId);
    }

    public function delete_table($id)
    {
        $this->db->delete(Constants::TABLE_TABLE, array('id' => $id));
    }
}