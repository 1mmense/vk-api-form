<?php

namespace App\Handlers;

use App\Support\Config;

class Handler
{
    public function getFilesFromFolder($isPhotos = true, $isForSrc = false)
    {
        $files = [];

        $folder = $isPhotos ? Config::UPLOAD_IMAGES_FOLDER : Config::UPLOAD_VIDEOS_FOLDER;

        if (!is_dir($folder)) {
            return $files;
        }

        $dirContent = array_diff(scandir($folder), ['..', '.', Config::GITKEEP_FILE]);
        $filesArray = array_values($dirContent);

        foreach ($filesArray as $fileKey => $file) {
            if ($isForSrc) {
                $files[$fileKey] = str_replace('../', '', $folder) . '/' . $file;
            } else {
                $files[$fileKey] = $folder . '/' . $file;
            }
        }

        return $files;
    }
}
