<?php

namespace affieasy;

class AFES_Constants
{
    const TABLE_WEBSHOP = 'wp_affieasy_webshop';
    const TABLE_TABLE = 'wp_affieasy_table';
    const TABLE_LINK = 'wp_affieasy_link';

    const ITEMS_PER_PAGE = 10;

    const TABLE_TAG = 'affieasy_table_content';
    const LINK_TAG = 'affieasy_link';

    const SHORT_LINK_SLUG = 'affieasy-link';

    const MANDATORY_URL_PARAM = 'product_url';

    const AFFIEASY_PLUGIN_VERSION = 'affieasy_plugin_version';

    const HTML = 'HTML';
    const IMAGE = 'IMAGE';
    const AFFILIATION = 'AFFILIATION';

    const COLUMN = 'COLUMN';
    const ROW = 'ROW';

    const HEADER_FONT_WEIGHTS = array('normal', 'bold', 'bolder');

    const HEADERS_TYPES = array(
        'COLUMN_HEADER' => 'Column header',
        'ROW_HEADER' => 'Row header',
        'BOTH' => 'Both',
        'NONE' => 'None'
    );

    const AVAILABLE_ICONS = array(
        '%TICK%' => 'yes affieasy-icon-green',
        '%CROSS%' => 'no affieasy-icon-red',
        '%INFO%' => 'info affieasy-icon-blue',
        '%WARNING%' => 'warning affieasy-icon-orange',
        '%HEART%' => 'heart affieasy-icon-red',
        '%LOCK%' => 'lock affieasy-icon-black',
        '%EMPTY-STAR%' => 'star-empty affieasy-icon-yellow',
        '%FILLED-STAR%' => 'star-filled affieasy-icon-yellow',
    );

    const PLUGIN_VERSION = '1.1.0';
    
}