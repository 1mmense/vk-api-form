<?php

namespace App\Controllers;

use App\Classes\VkApiService;
use App\Handlers\Handler;
use App\Handlers\PhotoHandler;
use App\Handlers\VideoHandler;
use App\Support\Config;

class UploadController
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

        if ($mode === 'get_files_from_folder') {
            $this->getFilesFromFolder();
        } elseif ($mode === 'save_photos') {
            $this->savePhotos();
        } elseif ($mode === 'save_videos') {
            $this->saveVideos();
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

    private function getFilesFromFolder(): void
    {
        $photos = $this->handler->getFilesFromFolder(true, true);
        $offset = $_REQUEST['offset'] ?? 0;
        $file   = $photos[$offset] ?? null;

        if (empty($file)) {
            echo json_encode(null);

            return;
        }

        echo json_encode([
            'file_path' => $file,
        ]);
    }

    private function savePhotos(): void
    {
        $fields = [];

        if (!empty($_REQUEST['file_path'])) {
            $fields['file_path'] = $_REQUEST['file_path'];
        }

        if (!empty($_REQUEST['is_post_all_photos'])
            && ($_REQUEST['is_post_all_photos'] === true || $_REQUEST['is_post_all_photos'] === 'true')
        ) {
            unset($fields['file_path']);
        }

        $result = $this->photoHandler->savePhotoToAlbum($fields);

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

    private function saveVideos(): void
    {
        $fields = [];

        if (!empty($_REQUEST['file_path'])) {
            $fields['file_path'] = $_REQUEST['file_path'];
        }

        $result = $this->videoHandler->saveVideoToAlbum($fields);

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
        $this->videoHandler->cleanupOrphanThumbs();

        $cmd = sprintf(
            'nohup php %s > /dev/null 2>&1 &',
            escapeshellarg(__DIR__ . '/../../generateThumbs.php')
        );
        exec($cmd);

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
        $this->videoHandler->cleanupOrphanThumbs();
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
}
