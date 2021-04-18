<?php

namespace affieasy;

class AFES_DbManager
{
    private $db;

    function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    public static function get_instance() {
        return new AFES_DbManager();
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

    public function drop_table($tableName)
    {
        $this->db->query('DROP TABLE IF EXISTS ' . $tableName);
    }

    /****************************** Webshop functions ******************************/

    public function create_table_webshop()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta(" CREATE TABLE " . AFES_Constants::TABLE_WEBSHOP . " (
			    id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
				name VARCHAR(255) NOT NULL,
				url TEXT NOT NULL,
				linkTextPreference VARCHAR(255),
				backgroundColorPreference VARCHAR(10),
				textColorPreference VARCHAR(10)
			);");
    }

    public function get_webshop_list()
    {
        return array_map(function ($webshop) {
            return new AFES_Webshop(
                intval($webshop['id']),
                $webshop['name'],
                $webshop['url'],
                $webshop['linkTextPreference'],
                $webshop['backgroundColorPreference'],
                $webshop['textColorPreference']
            );
        }, $this->db->get_results('SELECT * FROM ' . AFES_Constants::TABLE_WEBSHOP, ARRAY_A));
    }

    public function get_webshop_page($currentPage, $perPage)
    {
        $sql = $this->db->prepare(
            "SELECT id, name FROM " . AFES_Constants::TABLE_WEBSHOP . " ORDER BY id DESC LIMIT %d, %d",
            array((($currentPage - 1) * $perPage), $perPage));

        return $this->db->get_results($sql, ARRAY_A);
    }

    public function get_webshop_by_id($id)
    {
        $sql = $this->db->prepare("SELECT * FROM " . AFES_Constants::TABLE_WEBSHOP . " WHERE id=%d", array($id));
        $webshop = $this->db->get_row($sql);

        return new AFES_Webshop(
            $webshop->id,
            $webshop->name,
            $webshop->url,
            $webshop->linkTextPreference,
            $webshop->backgroundColorPreference,
            $webshop->textColorPreference
        );
    }

    public function edit_webshop($webshop)
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $webshopId = $webshop->getId();

        $canUsePremiumCode = false;
        if (aff_fs()->is__premium_only()) {
            if (aff_fs()->can_use_premium_code()) {
                $canUsePremiumCode = true;
            }
        }

        if (!$canUsePremiumCode && $webshopId === null && $this->get_table_count(AFES_Constants::TABLE_WEBSHOP) >= 2) {
            return new AFES_Webshop();
        }

        $values = array(
            "name" => $webshop->getName(),
            "url" => $webshop->getUrl(),
            "linkTextPreference" => $webshop->getLinkTextPreference(),
            "backgroundColorPreference" => $webshop->getBackgroundColorPreference(),
            "textColorPreference" => $webshop->getTextColorPreference());

        if (empty($webshopId)) {
            $this->db->insert(AFES_Constants::TABLE_WEBSHOP, $values);

            $webshopId = $this->db->insert_id;
        } else {
            $this->db->update(AFES_Constants::TABLE_WEBSHOP, $values, array("id" => $webshopId));
        }

        return $this->get_webshop_by_id($webshopId);
    }

    public function delete_webshop($id)
    {
        $this->db->delete(AFES_Constants::TABLE_WEBSHOP, array('id' => $id));
        $this->remove_affiliate_links_in_table_by_webshop_id($id);
    }

    /****************************** Table functions ******************************/

    public function create_table_table()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta("CREATE TABLE " . AFES_Constants::TABLE_TABLE . " (
			    id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
				name VARCHAR(255) NOT NULL,
				headerType ENUM('COLUMN_HEADER', 'ROW_HEADER', 'BOTH', 'NONE') NOT NULL DEFAULT 'COLUMN_HEADER',
				headerOptions JSON NOT NULL,
				content JSON NOT NULL,
				responsiveBreakpoint INTEGER,
				maxWidth INTEGER,
				backgroundColor VARCHAR(10)
			);");
    }

    public function get_table_list()
    {
        return array_map(function ($table) {
            return new AFES_Table(
                intval($table['id']),
                $table['name'],
                $table['headerType'],
                json_decode($table['headerOptions']),
                $this->table_row_content_to_table_content($table['content']),
                $table['responsiveBreakpoint'],
                $table['maxWidth'],
                $table['backgroundColor']
            );
        }, $this->db->get_results('SELECT * FROM ' . AFES_Constants::TABLE_TABLE, ARRAY_A));
    }

    public function get_table_page($currentPage, $perPage)
    {
        $sql = $this->db->prepare(
            "SELECT id, name FROM " . AFES_Constants::TABLE_TABLE . " ORDER BY id DESC LIMIT %d, %d",
            array((($currentPage - 1) * $perPage), $perPage));

        return $this->db->get_results($sql, ARRAY_A);
    }

    public function get_table_by_id($id)
    {
        $sql = $this->db->prepare("SELECT * FROM " . AFES_Constants::TABLE_TABLE . " WHERE id=%d", array($id));
        $table = $this->db->get_row($sql);

        return isset($table->id) ? new AFES_Table(
            $table->id,
            $table->name,
            $table->headerType,
            json_decode($table->headerOptions),
            $this->table_row_content_to_table_content($table->content),
            $table->responsiveBreakpoint,
            $table->maxWidth,
            $table->backgroundColor
        ) : new AFES_Table();
    }

    public function edit_table($table)
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $tableId = $table->getId();
        $responsiveBreakpoint = $table->getResponsiveBreakpoint();
        $maxWidth = $table->getMaxWidth();
        $backgroundColor = $table->getBackgroundColor();
        $values = array(
            "name" => $table->getName(),
            "headerType" => $table->getHeaderType(),
            "headerOptions" => json_encode($table->getHeaderOptions()),
            "content" => json_encode($table->getContent()),
            "maxWidth" => null,
            "responsiveBreakpoint" => AFES_Table::$defaultResponsiveBreakpoint,
            "backgroundColor" => AFES_Table::$defaultBackgroundColor);

        if (aff_fs()->is__premium_only()) {
            if (aff_fs()->can_use_premium_code()) {
                $values['maxWidth'] = is_numeric($maxWidth) ? $maxWidth : null;
                $values['responsiveBreakpoint'] = is_numeric($responsiveBreakpoint) ? $responsiveBreakpoint : null;
                $values['backgroundColor'] = $backgroundColor ? $backgroundColor : null;
            }
        }

        if (empty($tableId)) {
            $this->db->insert(AFES_Constants::TABLE_TABLE, $values);

            $tableId = $this->db->insert_id;
        } else {
            $this->db->update(AFES_Constants::TABLE_TABLE, $values, array("id" => $tableId));
        }

        return $this->get_table_by_id($tableId);
    }

    public function delete_table($id)
    {
        $this->db->delete(AFES_Constants::TABLE_TABLE, array('id' => $id));
    }

    private function remove_affiliate_links_in_table_by_webshop_id($id)
    {
        foreach ($this->get_table_list() as $table) {
            $shouldUpdate = false;
            $updatedContent = array();

            foreach ($table->getContent() as $rows) {
                $updatedRow = array();
                $isFirst = true;

                foreach ($rows as $cell) {
                    if ($cell->type === AFES_Constants::AFFILIATION && (!in_array($table->getHeaderType(), array('ROW_HEADER', 'BOTH')) || !$isFirst)) {
                        $affiliateLinks = array();

                        foreach (json_decode(str_replace("&quot;", '"', $cell->value)) as $affiliateLink) {
                            if ($affiliateLink->webshopId == $id) {
                                $shouldUpdate = true;
                            } else {
                                array_push($affiliateLinks, $affiliateLink);
                            }
                        }

                        $cell->value = str_replace('\\', '', str_replace('"' , '&quot;', json_encode($affiliateLinks)));
                    }

                    $isFirst = false;
                    array_push($updatedRow, $cell);
                }
                array_push($updatedContent, $updatedRow);
            }

            $table->setContent($updatedContent);

            if ($shouldUpdate) {
                $table->setContent($updatedContent);
                $this->edit_table($table);
            }
        }
    }

    /****************************** Link functions ******************************/

    public function create_table_link()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta(" CREATE TABLE " . AFES_Constants::TABLE_LINK . " (
			    id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
				webshopId INTEGER,
			    label VARCHAR(255) NOT NULL,
			    category VARCHAR(255) NOT NULL,
				parameters JSON NOT NULL,
			    url TEXT NOT NULL,
				noFollow BOOLEAN NOT NULL DEFAULT TRUE,
				openInNewTab BOOLEAN NOT NULL DEFAULT TRUE,
				FOREIGN KEY (webshopId) REFERENCES " . AFES_Constants::TABLE_WEBSHOP . "(id) ON DELETE CASCADE
			);");
    }

    public function get_link_count($search)
    {
        $sqlSearch = '';
        $sqlParameters = array();
        if (isset($search)) {
            $sqlSearch = "WHERE tw.name LIKE CONCAT('%',%s,'%') OR tl.label LIKE CONCAT('%',%s,'%') OR tl.category LIKE CONCAT('%',%s,'%') OR tl.url LIKE CONCAT('%',%s,'%')";
            array_push($sqlParameters, $search, $search, $search, $search);

            if (is_numeric($search)) {
                $sqlSearch .= "OR tl.id = %d";
                array_push($sqlParameters, intval($search));
            }
        }

        $sql = $this->db->prepare(
            "SELECT COUNT(*) AS number
            FROM " . AFES_Constants::TABLE_LINK . " tl
            INNER JOIN " . AFES_Constants::TABLE_WEBSHOP . " tw  
            ON tl.webshopId = tw.id " . $sqlSearch,
            $sqlParameters);

        return intval($this->db->get_results($sql, ARRAY_A)[0]['number']);
    }

    public function get_link_page($currentPage, $perPage, $orderBy, $order, $search)
    {
        switch ($orderBy) {
            case 'webshop':
                $orderBy = 'tw.name';
                break;
            case 'label':
                $orderBy = 'tl.label';
                break;
            case 'category':
                $orderBy = 'tl.category';
                break;
            case 'url':
                $orderBy = 'tl.url';
                break;
            default :
                $orderBy = 'tl.id';
        }

        $order = in_array($order, array('asc', 'desc')) ? $order : 'asc';

        $sqlSearch = '';
        $sqlParameters = array();
        if (isset($search)) {
            $sqlSearch = "WHERE tw.name LIKE CONCAT('%',%s,'%') OR tl.label LIKE CONCAT('%',%s,'%') OR tl.category LIKE CONCAT('%',%s,'%') OR tl.url LIKE CONCAT('%',%s,'%')";
            array_push($sqlParameters, $search, $search, $search, $search);

            if (is_numeric($search)) {
                $sqlSearch .= "OR tl.id = %d";
                array_push($sqlParameters, intval($search));
            }
        }

        array_push($sqlParameters, (($currentPage - 1) * $perPage), $perPage);

        $sql = $this->db->prepare(
            "SELECT tl.id as id, CONCAT('[" . AFES_Constants::LINK_TAG . " id=', tl.id, ']') as tag, tw.name as webshop, tl.webshopId as webshopId, tl.label as label, tl.category as category, tl.parameters as parameters, tl.url as url, tl.noFollow as noFollow, tl.openInNewTab as openInNewTab  
            FROM " . AFES_Constants::TABLE_LINK . " tl
            INNER JOIN " . AFES_Constants::TABLE_WEBSHOP . " tw  
            ON tl.webshopId = tw.id " . $sqlSearch . " 
            ORDER BY " . $orderBy . " " . $order .  " LIMIT %d, %d",
            $sqlParameters);

        return $this->db->get_results($sql, ARRAY_A);
    }

    public function get_link_by_id($id)
    {
        if (!isset($id) || !is_numeric($id)) {
            return new AFES_Link();
        }

        $sql = $this->db->prepare("SELECT * FROM " . AFES_Constants::TABLE_LINK . " WHERE id=%d", array($id));
        $link = $this->db->get_row($sql);

        return isset($link->id) ? new AFES_Link(
            $link->id,
            $link->webshopId,
            $link->label,
            $link->category,
            $link->parameters,
            $link->url,
            $link->noFollow,
            $link->openInNewTab
        ) : new AFES_Link();
    }

    public function edit_link($link)
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $id = $link->getId();

        $canUsePremiumCode = false;
        if (aff_fs()->is__premium_only()) {
            if (aff_fs()->can_use_premium_code()) {
                $canUsePremiumCode = true;
            }
        }

        if (!$canUsePremiumCode && $id === null && $this->get_table_count(AFES_Constants::TABLE_LINK) >= 50) {
            return new AFES_Link();
        }

        $values = array(
            "id" => $id,
            "webshopId" => $link->getWebshopId(),
            "label" => $link->getLabel(),
            "category" => $link->getCategory(),
            "parameters" => json_encode($link->getParameters()),
            "url" => $link->getUrl(),
            "noFollow" => $link->isNoFollow(),
            "openInNewTab" => $link->isOpenInNewTab()
        );

        if (isset($id)) {
            $this->db->update(AFES_Constants::TABLE_LINK, $values, array("id" => $id));
        } else {
            $this->db->insert(AFES_Constants::TABLE_LINK, $values);
        }
    }

    public function delete_link($id) {
        $this->db->delete(AFES_Constants::TABLE_LINK, array('id' => $id));
    }

    /****************************** Utils functions ******************************/
    private function table_row_content_to_table_content($tableRowContent)
    {
        return empty($tableRowContent) ? null : array_map(function ($row) {
            return array_map(function ($cell) {
                return $cell;
            }, $row);
        }, json_decode($tableRowContent));
    }

}