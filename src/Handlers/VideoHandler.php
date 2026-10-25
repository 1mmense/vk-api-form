<?php

declare(strict_types=1);

namespace App\Handlers;

use App\Support\Config;
use App\Classes\VkApiService;
use App\Support\FileStorage;
use App\Support\Functions;

class VideoHandler
{
    public function __construct(
        private VkApiService $vkApi,
        private FileStorage $fileStorage,
    ) {
    }

    public function uploadVideo(array $params = []): array
    {
        $result = [
            'success' => false,
            'error'   => null
        ];

        $isMoveAfterUpload = (bool) $params['isMoveAfterUpload'] ?? false;
        $isPostToWall      = (bool) $params['isPostVideosToWall'] ?? false;
        $filePath          = (string) $params['filePath'] ?? '';

        if (empty($filePath) || !file_exists($filePath)) {
            $result['error'] = 'No such file found: ' . $filePath;

            return $result;
        }

        $extraPostFields = [
            'group_id'  => $_ENV['GROUP_ID'],
            'album_id'  => $_ENV['ALBUM_ID'],
            'file_path' => $filePath
        ];

        $pathinfo = pathinfo($filePath);

        $this->vkApi->postData[CURLOPT_POSTFIELDS] = array_merge($this->vkApi->postFields, [
            'group_id'    => $extraPostFields['group_id'],
            'repeat'      => 1,
            'no_comments' => 0,
            'name'        => $pathinfo['basename'],
        ]);

        $this->vkApi->postData[CURLOPT_URL] = Config::URL_VIDEOS_SAVE;

        $getUploadUrlResult = $this->vkApi->executePostQuery();

        if (!empty($getUploadUrlResult['error'])) {
            $result['error'] = $getUploadUrlResult['error'];

            return $result;
        }

        $uploadUrl = $getUploadUrlResult['response']['upload_url'] ?? null;

        if (empty($uploadUrl)) {
            $result['error'] = 'upload_url is empty';

            return $result;
        }

        $this->vkApi->postData[CURLOPT_URL] = $uploadUrl;

        $this->vkApi->postData[CURLOPT_POSTFIELDS] = [
            'video_file' => new \CURLFile(
                $filePath,
                mime_content_type($filePath),
                $pathinfo['basename']
            ),
        ];

        $uploadResult = $this->vkApi->executePostQuery();

        if (!empty($uploadResult['error'])) {
            $result['error'] = $uploadResult['error'];

            return $result;
        }

        if (empty($uploadResult['size'])) {
            $result['error'] = 'upload result has no size field';

            return $result;
        }

        if ($isPostToWall
            && !empty($uploadResult['video_id'])
        ) {
            $wallPostFields = [
                'attachments' => 'video-' . $_ENV['GROUP_ID'] . '_' . $uploadResult['video_id'],
            ];

            $wallPostResult = $this->vkApi->postToWall($wallPostFields);

            if (!empty($wallPostResult['error'])) {
                $result['error'] = $wallPostResult['error'];

                return $result;
            }
        }

        if ($isMoveAfterUpload) {
            $destination = Config::DONE_VIDEOS_FOLDER . '/' . $pathinfo['basename'];

            if (!copy($filePath, $destination) || !file_exists($destination)) {
                $result['error'] = 'failed to move file to done folder';

                return $result;
            }

            unlink($filePath);

            $thumbFile = $this->fileStorage->getThumbFile($pathinfo['filename']);

            if (file_exists($thumbFile)) {
                unlink($thumbFile);
            }

            $this->removeOrphanThumbs();
        }

        $result['success'] = true;

        return $result;
    }

    public function removeOrphanThumbs(): void
    {
        $thumbsDir = Config::basePath() . '/' . Config::FILES_FOLDER . '/' . Config::VIDEO_THUMBS_FOLDER;
        $videosDir = Config::basePath() . '/' . Config::FILES_FOLDER . '/' . Config::UPLOAD_VIDEOS_FOLDER;

        if (!is_dir($thumbsDir) || !is_dir($videosDir)) {
            return;
        }

        $scanDir = function ($dirName) {
            $files = scandir($dirName);

            if ($files === false) {
                return [];
            }

            return array_diff($files, ['..', '.', Config::GITKEEP_FILE]);
        };

        // Собираем список базовых имён видео (без расширения)
        $videoNames = [];
        $videoFiles = $scanDir($videosDir);

        foreach ($videoFiles as $file) {
            $pathinfo                          = pathinfo($file);
            $videoNames[$pathinfo['filename']] = true;
        }

        // Проходим по превью и удаляем сиротские
        $thumbFiles = $scanDir($thumbsDir);

        foreach ($thumbFiles as $file) {
            // Превью называются thumbnail_<filename>.webp
            if (!str_starts_with($file, 'thumbnail_')) {
                continue;
            }

            $baseName = substr($file, strlen('thumbnail_'));
            $baseName = pathinfo($baseName, PATHINFO_FILENAME);

            if (!isset($videoNames[$baseName])) {
                $thumbFile = $thumbsDir . '/' . $file;

                if (file_exists($thumbFile)) {
                    unlink($thumbFile);
                }
            }
        }
    }

    // TODO: Рефакторинг
    public function startThumbGeneration()
    {
        $cmd = sprintf(
            'nohup php %s > /dev/null 2>&1 &',
            escapeshellarg(__DIR__ . '/../../generate_thumbs.php')
        );

        exec($cmd);
    }

    public function getVideoThumbs(): array
    {
        $this->removeOrphanThumbs();

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

        return $result;
    }
}
