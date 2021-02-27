<?php

class Utils
{
    static function get_plugin_name()
    {
        return strpos(dirname(__DIR__), '-premium') === false ? 'affieasy' : 'affieasy-premium';
    }

    static function sanitize_header_options($headerOptions)
    {
        $sanitizedHeaderOptions = new stdClass();

        foreach (json_decode(str_replace('\\', '', $headerOptions)) as $key => $value) {
            $sanitizedHeaderOptions->{sanitize_key($key)} = strpos($key, 'color') !== false || strpos($key, 'background') !== false  ?
                sanitize_hex_color($value) :
                sanitize_key($value);;
        }

        return $sanitizedHeaderOptions;
    }

    static function sanitize_content($content) {
        return array_map(function ($row) {
            return array_map(function ($cell) {
                $cellContent = json_decode(
                    str_replace("\\", "",
                        str_replace('\\\\\\"', "&quot;",
                            str_replace('\\n', '&NewLine;', $cell))));

                $type = isset($cellContent->type) ? sanitize_text_field($cellContent->type) : null;
                $value = isset($cellContent->value) ? $cellContent->value : null;
                if (isset($value)) {
                    if ($type === Constants::AFFILIATION) {
                        $value = Utils::sanitize_links(json_decode(
                            str_replace("\\", "",
                                str_replace('\\\\\\"', "&quot;",
                                    str_replace('\\\\\\\\\\\\\\"', '',
                                        str_replace('\\n', '&NewLine;', $cell)))))->value);
                    } else {
                        $value = str_replace(
                            '"',
                            "&quot;",
                            wp_kses(str_replace("&quot;", '"', $value), wp_kses_allowed_html('post')));
                    }
                }

                return (object)[
                    'type' => $type,
                    'value' => $value,
                ];
            }, $row);
        }, $content);
    }

    static function sanitize_links($linksString)
    {
        $links = json_decode(str_replace('&quot;', '"', $linksString));
        if ($links == null || empty($links)) {
            return $linksString;
        }

        $sanitizedLinks = array();

        foreach ($links as $link) {
            $sanitizedLink = array();

            foreach($link as $key => $value) {
                $key = sanitize_text_field($key);

                if ($key === 'url') {
                    $value = esc_url_raw($value);
                } else if (in_array($key, array('color', 'background'))) {
                    $value = sanitize_hex_color($value);
                } else {
                    $value = sanitize_text_field($value);
                }
                $value = $key === 'url' ? esc_url_raw($value) : sanitize_text_field($value);

                $sanitizedLink[$key] = $value;
            }

            array_push($sanitizedLinks, (object) $sanitizedLink);
        }

        return str_replace('"', '&quot;', json_encode($sanitizedLinks));
    }
}