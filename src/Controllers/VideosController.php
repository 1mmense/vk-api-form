<?php

namespace App\Controllers;

use App\Classes\VkApiService;
use App\Handlers\Handler;
use App\Handlers\VideoHandler;
use App\Support\Config;

class VideosController
{
    private VkApiService $vkApi;
    private Handler $handler;
    private VideoHandler $videoHandler;

    public function __construct()
    {
        $this->vkApi        = new VkApiService();
        $this->handler      = new Handler($this->vkApi);
        $this->videoHandler = new VideoHandler($this->vkApi);
    }

    public function index(): void
    {
        $this->videoHandler->removeOrphanThumbs();
        $this->videoHandler->startThumbGeneration();

        $videos = $this->handler->getFilesFromFolder(false, true);
        $result = [];

        foreach ($videos as $videoPath) {
            $pathinfo   = pathinfo($videoPath);
            $thumb_path = Config::VIDEO_THUMBS_FOLDER . '/thumbnail_' . $pathinfo['filename'] . '.webp';

            $result[] = [
                'path'      => $videoPath,
                'basename'  => basename($videoPath),
                'thumbnail' => file_exists($thumb_path)
                    ? str_replace('../', '', $thumb_path)
                    : null,
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
