<?php

namespace App\Handlers;

use App\Support\Config;
use App\Classes\VkApiService;

class VideoHandler extends Handler
{
    protected VkApiService $vkApi;

    public function __construct(VkApiService $vkApi)
    {
        $this->vkApi = $vkApi;
    }

    public function uploadVideo(
        array $extraPostFields = [],
        bool $isRemoveAfterUpload = true,
        bool $isPostToWall = false
    ): array {
        $result = [
            'success' => false,
            'error'   => null
        ];

        $defaultFields = [
            'group_id' => $_ENV['GROUP_ID'],
            'album_id' => $_ENV['ALBUM_ID']
        ];

        $extraPostFields = array_merge($defaultFields, $extraPostFields);

        if (empty($extraPostFields['file_path'])) {
            $files = $this->getFilesFromFolder(false);
        } else {
            $files = [$extraPostFields['file_path']];
        }

        if (empty($files)) {
            $result['error'] = 'no files to upload';

            return $result;
        }

        foreach ($files as $fileKey => $filePath) {
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

            if ($isRemoveAfterUpload
                && file_exists($filePath)
            ) {
                $destination = Config::DONE_VIDEOS_FOLDER . '/' . $pathinfo['basename'];

                if (!copy($filePath, $destination) || !file_exists($destination)) {
                    $result['error'] = 'failed to move file to done folder';

                    return $result;
                }

                unlink($filePath);

                $thumbPath = Config::VIDEO_THUMBS_FOLDER . '/' . $pathinfo['filename'] . '.webp';

                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }
            }

            $result['success'] = true;

            $this->removeOrphanThumbs();

            return $result;
        }
    }

    public function removeOrphanThumbs(): void
    {
        $thumbsDir = Config::VIDEO_THUMBS_FOLDER;
        $videosDir = Config::UPLOAD_VIDEOS_FOLDER;

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
                $thumbPath = $thumbsDir . '/' . $file;

                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
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

        $videos = $this->getFilesFromFolder(false, true);
        $result = [];

        foreach ($videos as $videoPath) {
            $pathinfo   = pathinfo($videoPath);
            $thumbPath = Config::VIDEO_THUMBS_FOLDER . '/thumbnail_' . $pathinfo['filename'] . '.webp';

            $result[] = [
                'path'      => $videoPath,
                'basename'  => basename($videoPath),
                'thumbnail' => file_exists($thumbPath)
                    ? str_replace('../', '', $thumbPath)
                    : null,
            ];
        }

        return $result;
    }
}
