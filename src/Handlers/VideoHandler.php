<?php

namespace App\Handlers;

use App\Support\Config;
use App\Classes\VkApiService;

class VideoHandler extends Handler
{
    protected VkApiService $vk_api;

    public function __construct(VkApiService $vk_api)
    {
        $this->vk_api = $vk_api;
    }

    public function uploadVideo(
        array $extra_post_fields = [],
        bool $isRemoveAfterUpload = true,
        bool $isPostToWall = false
    ): array {
        $result = ['success' => false, 'error' => null];

        $default_fields = [
            'group_id' => $_ENV['GROUP_ID'],
            'album_id' => $_ENV['ALBUM_ID']
        ];

        $extra_post_fields = array_merge($default_fields, $extra_post_fields);

        if (empty($extra_post_fields['file_path'])) {
            $files = $this->getFilesFromFolder(false);
        } else {
            $files = [$extra_post_fields['file_path']];
        }

        if (empty($files)) {
            $result['error'] = 'no files to upload';

            return $result;
        }

        foreach ($files as $file_key => $file_path) {
            $pathinfo = pathinfo($file_path);

            $this->vk_api->post_data[CURLOPT_POSTFIELDS] = array_merge($this->vk_api->post_fields, [
                'group_id'    => $extra_post_fields['group_id'],
                'repeat'      => 1,
                'no_comments' => 0,
                'name'        => $pathinfo['basename'],
            ]);

            $this->vk_api->post_data[CURLOPT_URL] = Config::URL_VIDEOS_SAVE;

            $get_upload_url_result = $this->vk_api->executePostQuery();

            if (!empty($get_upload_url_result['error'])) {
                $result['error'] = $get_upload_url_result['error'];

                return $result;
            }

            $upload_url = $get_upload_url_result['response']['upload_url'] ?? null;

            if (empty($upload_url)) {
                $result['error'] = 'upload_url is empty';

                return $result;
            }

            $this->vk_api->post_data[CURLOPT_URL] = $upload_url;

            $this->vk_api->post_data[CURLOPT_POSTFIELDS] = [
                'video_file' => new \CURLFile(
                    $file_path,
                    mime_content_type($file_path),
                    $pathinfo['basename']
                ),
            ];

            $upload_result = $this->vk_api->executePostQuery();

            if (!empty($upload_result['error'])) {
                $result['error'] = $upload_result['error'];

                return $result;
            }

            if (empty($upload_result['size'])) {
                $result['error'] = 'upload result has no size field';

                return $result;
            }

            if ($isPostToWall
                && !empty($upload_result['video_id'])
            ) {
                $wallPostFields = [
                    'attachments' => 'video-' . $_ENV['GROUP_ID'] . '_' . $upload_result['video_id'],
                ];

                $wallResult = $this->vk_api->post($wallPostFields);

                if (!empty($wallResult['error'])) {
                    $result['error'] = $wallResult['error'];

                    return $result;
                }
            }

            if ($isRemoveAfterUpload
                && file_exists($file_path)
            ) {
                $destination = Config::DONE_VIDEOS_FOLDER . '/' . $pathinfo['basename'];

                if (!copy($file_path, $destination) || !file_exists($destination)) {
                    $result['error'] = 'failed to move file to done folder';

                    return $result;
                }

                unlink($file_path);

                $thumb_path = Config::VIDEO_THUMBS_FOLDER . '/' . $pathinfo['filename'] . '.jpg';

                if (file_exists($thumb_path)) {
                    unlink($thumb_path);
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
            escapeshellarg(__DIR__ . '/../../generateThumbs.php')
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
            $thumb_path = Config::VIDEO_THUMBS_FOLDER . '/thumbnail_' . $pathinfo['filename'] . '.webp';

            $result[] = [
                'path'      => $videoPath,
                'basename'  => basename($videoPath),
                'thumbnail' => file_exists($thumb_path)
                    ? str_replace('../', '', $thumb_path)
                    : null,
            ];
        }

        return $result;
    }
}
