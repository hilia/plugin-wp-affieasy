<?php

class Utils
{
    static function get_plugin_name() {
        return strpos(dirname(__DIR__), '-premium') === false ? 'affieasy' : 'affieasy-premium';
    }

    static function remove_directory($directory)
    {
        $recursive = new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($recursive, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($directory);
    }
}