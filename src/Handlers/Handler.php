<?php

namespace App\Handlers;

use App\Support\Config;

class Handler
{
    public function getFilesFromFolder($is_photos = true, $is_for_src = false)
    {
        $files = [];

        $folder = $is_photos ? Config::UPLOAD_IMAGES_FOLDER : Config::UPLOAD_VIDEOS_FOLDER;

        if (!is_dir($folder)) {
            return $files;
        }

        $dir_content = array_diff(scandir($folder), ['..', '.', Config::GITKEEP_FILE]);
        $files_array = array_values($dir_content);

        foreach ($files_array as $file_key => $file) {
            if ($is_for_src) {
                $files[$file_key] = str_replace('../', '', $folder) . '/' . $file;
            } else {
                $files[$file_key] = $folder . '/' . $file;
            }
        }

        return $files;
    }
}
