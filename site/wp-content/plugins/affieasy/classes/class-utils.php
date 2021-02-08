<?php

class Utils
{
    static function get_plugin_name() {
        return strpos(dirname(__DIR__), '-premium') === false ? 'affieasy' : 'affieasy-premium';
    }
}