<?php

declare(strict_types=1);

namespace App\Handlers;

use App\Support\Config;
use App\Classes\VkApiService;
use App\Support\FileStorage;
use App\Classes\VkConfig;

class ImageHandler
{
    public function __construct(
        private VkApiService $vkApi,
        private FileStorage $fileStorage,
        private VkConfig $vkConfig
    ) {
    }

    public function getImages(): array
    {
        $images = $this->fileStorage->listUrls(Config::UPLOAD_IMAGES_FOLDER);
        $result = [];

        foreach ($images as $imagePath) {
            $result[] = [
                'path'     => $imagePath,
                'basename' => basename($imagePath),
            ];
        }

        return $result;
    }

    public function uploadImage(array $params = []): array
    {
        $result = [
            'success' => false,
            'error'   => null,
        ];

        $isMoveAfterUpload = (bool) ($params['isMoveAfterUpload'] ?? false);
        $isPostToWall      = (bool) ($params['isPostImagesToWall'] ?? false);
        $filePath          = (string) ($params['filePath'] ?? '');

        if ($filePath === '' || !file_exists($filePath)) {
            $result['error'] = 'No such file found: ' . $filePath;

            return $result;
        }

        $extraPostFields = [
            'group_id'  => $this->vkConfig->groupId,
            'album_id'  => $this->vkConfig->albumId,
            'file_path' => $filePath
        ];

        $getUploadUrlResult = $this->vkApi->executePostQuery(
            url: Config::URL_GET_IMAGES_UPLOAD_SERVER,
            postFields: [
                'group_id' => $extraPostFields['group_id'],
                'album_id' => $extraPostFields['album_id'],
            ],
        );

        $uploadUrl = $getUploadUrlResult['response']['upload_url'] ?? null;

        if (empty($uploadUrl)) {
            $result['error'] = 'upload_url is empty';

            return $result;
        }

        $pathinfo = pathinfo($filePath);

        $uploadResult = $this->vkApi->executePostQuery(
            url: $uploadUrl,
            postFields: [
                'file1' => new \CURLFile(
                    $filePath,
                    mime_content_type($filePath),
                    $pathinfo['basename']
                ),
            ],
        );

        $server     = $uploadResult['server'] ?? null;
        $imagesList = $uploadResult['photos_list'] ?? null;
        $albumId    = $uploadResult['aid'] ?? null;
        $hash       = $uploadResult['hash'] ?? null;

        if (!isset($server, $imagesList, $albumId, $hash)) {
            $result['error'] = 'upload_result missing fields';

            return $result;
        }

        $saveResult = $this->vkApi->executePostQuery(
            url: Config::URL_PHOTOS_SAVE,
            postFields: [
                'group_id'    => $extraPostFields['group_id'],
                'server'      => $server,
                'photos_list' => $imagesList,
                'album_id'    => $albumId,
                'hash'        => $hash,
            ],
        );

        if (!empty($saveResult['error'])) {
            $result['error'] = $saveResult['error'];

            return $result;
        }

        $savedImageData = reset($saveResult['response']);

        if (empty($savedImageData['id'])) {
            $result['error'] = 'Saved Image ID is empty';

            return $result;
        }

        if ($isPostToWall) {
            $wallPostFields = [
                'attachments' => 'photo-' . $this->vkConfig->groupId . '_' . $savedImageData['id'],
            ];

            $wallResult = $this->vkApi->postToWall($wallPostFields);

            if (!empty($wallResult['error'])) {
                $result['error'] = $wallResult['error'];

                return $result;
            }
        }

        if ($isMoveAfterUpload) {
            $destination = Config::DONE_IMAGES_FOLDER . '/' . $pathinfo['basename'];

            if (copy($filePath, $destination)
                && file_exists($destination)
            ) {
                unlink($filePath);
            } else {
                $result['error'] = 'failed to move file to done folder';

                return $result;
            }
        }

        $result['success'] = true;

        return $result;
    }
}
