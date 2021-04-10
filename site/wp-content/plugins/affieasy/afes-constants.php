<?php

namespace affieasy;

class AFES_Constants {
    const TABLE_WEBSHOP = 'wp_affieasy_webshop';
    const TABLE_TABLE = 'wp_affieasy_table';
    const TABLE_LINK = 'wp_affieasy_link';

    const ITEMS_PER_PAGE = 10;

    const TABLE_TAG = 'affieasy_table_content';
    const LINK_TAG = 'affieasy_link';

    const SHORT_LINK_SLUG = 'affieasy-link';

    const MANDATORY_URL_PARAM = 'product_url';

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
}