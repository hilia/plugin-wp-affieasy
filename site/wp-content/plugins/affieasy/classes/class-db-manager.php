<?php

class DbManager
{
    private $db;

    function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    public static function get_instance() {
        return new DbManager();
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

        dbDelta(" CREATE TABLE " . Constants::TABLE_WEBSHOP . " (
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
            return new Webshop(
                intval($webshop['id']),
                $webshop['name'],
                $webshop['url'],
                $webshop['linkTextPreference'],
                $webshop['backgroundColorPreference'],
                $webshop['textColorPreference']
            );
        }, $this->db->get_results('SELECT * FROM ' . Constants::TABLE_WEBSHOP, ARRAY_A));
    }

    public function get_webshop_page($currentPage, $perPage)
    {
        $sql = $this->db->prepare(
            "SELECT id, name FROM " . Constants::TABLE_WEBSHOP . " ORDER BY id DESC LIMIT %d, %d",
            array((($currentPage - 1) * $perPage), $perPage));

        return $this->db->get_results($sql, ARRAY_A);
    }

    public function get_webshop_by_id($id)
    {
        $sql = $this->db->prepare("SELECT * FROM " . Constants::TABLE_WEBSHOP . " WHERE id=%d", array($id));
        $webshop = $this->db->get_row($sql);

        return new Webshop(
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

        if (!$canUsePremiumCode && $webshopId === null && $this->get_table_count(Constants::TABLE_WEBSHOP) >= 2) {
            return new Webshop();
        }

        $values = array(
            "name" => $webshop->getName(),
            "url" => $webshop->getUrl(),
            "linkTextPreference" => $webshop->getLinkTextPreference(),
            "backgroundColorPreference" => $webshop->getBackgroundColorPreference(),
            "textColorPreference" => $webshop->getTextColorPreference());

        if (empty($webshopId)) {
            $this->db->insert(Constants::TABLE_WEBSHOP, $values);

            $webshopId = $this->db->insert_id;
        } else {
            $this->db->update(Constants::TABLE_WEBSHOP, $values, array("id" => $webshopId));
        }

        return $this->get_webshop_by_id($webshopId);
    }

    public function delete_webshop($id)
    {
        $this->db->delete(Constants::TABLE_WEBSHOP, array('id' => $id));
        $this->remove_affiliate_links_in_table_by_webshop_id($id);
    }

    /****************************** Table functions ******************************/

    public function create_table_table()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta("CREATE TABLE " . Constants::TABLE_TABLE . " (
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
            return new Table(
                intval($table['id']),
                $table['name'],
                $table['headerType'],
                json_decode($table['headerOptions']),
                $this->table_row_content_to_table_content($table['content']),
                $table['responsiveBreakpoint'],
                $table['maxWidth'],
                $table['backgroundColor']
            );
        }, $this->db->get_results('SELECT * FROM ' . Constants::TABLE_TABLE, ARRAY_A));
    }

    public function get_table_page($currentPage, $perPage)
    {
        $sql = $this->db->prepare(
            "SELECT id, name FROM " . Constants::TABLE_TABLE . " ORDER BY id DESC LIMIT %d, %d",
            array((($currentPage - 1) * $perPage), $perPage));

        return $this->db->get_results($sql, ARRAY_A);
    }

    public function get_table_by_id($id)
    {
        $sql = $this->db->prepare("SELECT * FROM " . Constants::TABLE_TABLE . " WHERE id=%d", array($id));
        $table = $this->db->get_row($sql);

        return isset($table->id) ? new Table(
            $table->id,
            $table->name,
            $table->headerType,
            json_decode($table->headerOptions),
            $this->table_row_content_to_table_content($table->content),
            $table->responsiveBreakpoint,
            $table->maxWidth,
            $table->backgroundColor
        ) : new Table();
    }

    public function edit_table($table, $isUpdateFromWebshopEdition)
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $tableId = $table->getId();

        $responsiveBreakpoint = $table->getResponsiveBreakpoint();
        $maxWidth = $table->getMaxWidth();
        $backgroundColor = $table->getBackgroundColor();
        $values = array(
            "name" => $table->getName(),
            "headerType" => $table->getHeaderType(),
            "headerOptions" => $isUpdateFromWebshopEdition ?
                json_encode($table->getHeaderOptions()) :
                str_replace('\\', '', $table->getHeaderOptions()),
            "content" => json_encode($isUpdateFromWebshopEdition ? $table->getContent() : array_map(function ($row) {
                return array_map(function ($cell) {
                    return json_decode(
                        str_replace("\\", "",
                            str_replace('\\\\\\"', "&quot;",
                                str_replace('\\n', '&NewLine;', $cell))));
                }, $row);
            }, $table->getContent())),
            "maxWidth" => null,
            "responsiveBreakpoint" => Table::$defaultResponsiveBreakpoint,
            "backgroundColor" => Table::$defaultBackgroundColor);

        if (aff_fs()->is__premium_only()) {
            if (aff_fs()->can_use_premium_code()) {
                $values['maxWidth'] = is_numeric($maxWidth) ? $maxWidth : null;
                $values['responsiveBreakpoint'] = is_numeric($responsiveBreakpoint) ? $responsiveBreakpoint : null;
                $values['backgroundColor'] = $backgroundColor ? $backgroundColor : null;
            }
        }

        if (empty($tableId)) {
            $this->db->insert(Constants::TABLE_TABLE, $values);

            $tableId = $this->db->insert_id;
        } else {
            $this->db->update(Constants::TABLE_TABLE, $values, array("id" => $tableId));
        }

        return $this->get_table_by_id($tableId);
    }

    public function delete_table($id)
    {
        $this->db->delete(Constants::TABLE_TABLE, array('id' => $id));
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
                    if ($cell->type === Constants::AFFILIATION && (!in_array($table->getHeaderType(), array('ROW_HEADER', 'BOTH')) || !$isFirst)) {
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
                $this->edit_table($table, true);
            }
        }
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