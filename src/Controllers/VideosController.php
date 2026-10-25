<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Handlers\VideoHandler;
use App\Support\Config;
use App\Support\FileStorage;
use App\Support\Functions;

class VideosController
{
    public function __construct(
        private VideoHandler $videoHandler,
        private FileStorage $fileStorage
    ) {
    }

    public function index(): void
    {
        $this->videoHandler->removeOrphanThumbs();
        $this->videoHandler->startThumbGeneration();

        $videos = $this->fileStorage->listUrls(Config::UPLOAD_VIDEOS_FOLDER);

        $result = [];

        foreach ($videos as $videoPath) {
            $pathinfo  = pathinfo($videoPath);
            $thumbFile = $this->fileStorage->getThumbFile($pathinfo['filename']);
            $thumbUrl  = $this->fileStorage->getThumbUrl($pathinfo['filename']);

            $result[] = [
                'path'      => $videoPath,
                'basename'  => basename($videoPath),
                'thumbnail' => file_exists($thumbFile) ? $thumbUrl : null,
            ];
        }

        echo json_encode($result);
    }

    public function upload(): void
    {
        $params = [
            'filePath'           => filter_input(INPUT_POST, 'filePath'),
            'isPostVideosToWall' => filter_input(INPUT_POST, 'isPostVideosToWall', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'isMoveAfterUpload'  => filter_input(INPUT_POST, 'isMoveVideosAfterUpload', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
        ];

        if (empty($params['filePath'])) {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'error'   => "No 'filePath' specified",
            ]);

            return;
        }

        $result = $this->videoHandler->uploadVideo(params: $params);

        if ($result['success']) {
            echo json_encode([
                'success' => true
            ]);

            return;
        }

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'error'   => $result['error']
        ]);
    }

    public function thumbs(): void
    {
        $result = $this->videoHandler->getVideoThumbs();

        echo json_encode($result);
    }
}
