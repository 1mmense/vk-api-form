<?php

namespace App\Controllers;

use App\Classes\VkApiService;
use App\Handlers\Handler;
use App\Handlers\PhotoHandler;
use App\Handlers\VideoHandler;
use App\Support\Config;

class MainController
{
    private VkApiService $vkApi;
    private Handler $handler;
    private PhotoHandler $photoHandler;
    private VideoHandler $videoHandler;

    public function __construct()
    {
        $this->vkApi        = new VkApiService();
        $this->handler      = new Handler($this->vkApi);
        $this->photoHandler = new PhotoHandler($this->vkApi);
        $this->videoHandler = new VideoHandler($this->vkApi);
    }

    public function handleRequest(): void
    {
        $mode = $_REQUEST['mode'] ?? null;

        if ($mode === 'upload_photos') {
            $this->savePhotos();
        } elseif ($mode === 'upload_videos') {
            $this->uploadVideos();
        } elseif ($mode === 'get_all_photos') {
            $this->getAllPhotos();
        } elseif ($mode === 'get_all_videos') {
            $this->getAllVideos();
        } elseif ($mode === 'get_video_thumbs') {
            $this->getVideoThumbs();
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid mode specified']);
        }
    }

    private function savePhotos(): void
    {
        $fields = [];

        if (!empty($_REQUEST['file_path'])) {
            $fields['file_path'] = $_REQUEST['file_path'];
        }

        $result = $this->photoHandler->uploadImage(
            extra_post_fields: $fields,
            isRemoveAfterUpload: false,
            isPostToWall: false
        );

        if ($result['success']) {
            echo json_encode([
                'success' => true,
            ]);

            return;
        }

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'error'   => $result['error'],
        ]);
    }

    private function uploadVideos(): void
    {
        $fields = [];

        if (!empty($_REQUEST['file_path'])) {
            $fields['file_path'] = $_REQUEST['file_path'];
        }

        $result = $this->videoHandler->uploadVideo(
            extra_post_fields: $fields,
            isRemoveAfterUpload: false,
            isPostToWall: false
        );

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

    private function getAllPhotos(): void
    {
        $photos = $this->handler->getFilesFromFolder(true, true);

        $result = [];

        foreach ($photos as $photoPath) {
            $result[] = [
                'path'     => $photoPath,
                'basename' => basename($photoPath),
            ];
        }

        echo json_encode($result);
    }

    private function getAllVideos(): void
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

    private function getVideoThumbs(): void
    {
        $result = $this->videoHandler->getVideoThumbs();

        echo json_encode($result);
    }
}
