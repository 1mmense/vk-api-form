<?php

namespace App\Handlers;

use App\Support\Config;
use App\Classes\VkApiService;

class PhotoHandler extends Handler
{
    protected VkApiService $vkApi;

    public function __construct(VkApiService $vkApi)
    {
        $this->vkApi = $vkApi;
    }

    public function getPhotosUrls(array $photos): array
    {
        $result = [];

        if (empty($photos)) {
            return $result;
        }

        foreach ($photos as $key => $photo) {
            usort($photos[$key]['sizes'], function ($a, $b) {
                return $a['width'] < $b['width'];
            });

            $largestSize = reset($photos[$key]['sizes']);
            unset($photos[$key]['sizes']);
            $photos[$key]['url'] = $largestSize['url'];
        }

        $result = $photos;

        return $result;
    }

    public function uploadImage(
        array $extraPostFields,
        bool $isRemoveAfterUpload = true,
        bool $isPostToWall = true
    ): array {
        $result = [
            'success' => false,
            'error'   => null,
        ];

        $defaultFields = [
            'group_id' => $_ENV['GROUP_ID'],
            'album_id' => $_ENV['ALBUM_ID'],
        ];

        $extraPostFields = array_merge($defaultFields, $extraPostFields);

        if (empty($extraPostFields['file_path'])) {
            $files = $this->getFilesFromFolder();
        } else {
            $files = [$extraPostFields['file_path']];
        }

        if (empty($extraPostFields) || empty($files)) {
            return $result;
        }

        $getUploadServerFields = array_merge($this->vkApi->postFields, [
            'group_id' => $extraPostFields['group_id'],
            'album_id' => $extraPostFields['album_id'],
        ]);

        $this->vkApi->postData[CURLOPT_URL]        = Config::URL_GET_PHOTO_UPLOAD_SERVER;
        $this->vkApi->postData[CURLOPT_POSTFIELDS] = $getUploadServerFields;

        $getUploadUrlResult = $this->vkApi->executePostQuery();
        $uploadUrl            = $getUploadUrlResult['response']['upload_url'] ?? null;

        if (empty($uploadUrl)) {
            $result['error'] = 'upload_url is empty';

            return $result;
        }

        foreach ($files as $fileKey => $filePath) {
            $this->vkApi->postData[CURLOPT_URL]    = $uploadUrl;
            $this->vkApi->postData[CURLOPT_HEADER] = false;

            $pathinfo = pathinfo($filePath);

            if (empty($filePath) || !file_exists($filePath)) {
                $result['error'] = 'file not found: ' . $filePath;

                return $result;
            }

            $this->vkApi->postData[CURLOPT_POSTFIELDS] = [
                'file1' => new \CURLFile(
                    $filePath,
                    mime_content_type($filePath),
                    $pathinfo['basename']
                ),
            ];

            $upload_result = $this->vkApi->executePostQuery();
            $server        = $upload_result['server'] ?? null;
            $photos_list   = $upload_result['photos_list'] ?? null;
            $album_id      = $upload_result['aid'] ?? null;
            $hash          = $upload_result['hash'] ?? null;

            if (!isset($server, $photos_list, $album_id, $hash)) {
                $result['error'] = 'upload_result missing fields';

                return $result;
            }

            $this->vkApi->postData[CURLOPT_URL] = Config::URL_PHOTOS_SAVE;

            $this->vkApi->postData[CURLOPT_POSTFIELDS] = [
                'access_token' => $this->vkApi->postFields['access_token'],
                'v'            => $this->vkApi->postFields['v'],
                'group_id'     => $extraPostFields['group_id'],
                'server'       => $server,
                'photos_list'  => $photos_list,
                'album_id'     => $album_id,
                'hash'         => $hash,
            ];

            $saveResult = $this->vkApi->executePostQuery();

            if (!empty($saveResult['error'])) {
                $result['error'] = $saveResult['error'];

                return $result;
            }

            $saved_photo_data = reset($saveResult['response']);

            if (empty($saved_photo_data['id'])) {
                $result['error'] = 'saved photo id is empty';

                return $result;
            }

            if ($isPostToWall) {
                $wallPostFields = [
                    'attachments' => 'photo-' . $_ENV['GROUP_ID'] . '_' . $saved_photo_data['id'],
                ];

                $wallResult = $this->vkApi->postToWall($wallPostFields);

                if (!empty($wallResult['error'])) {
                    $result['error'] = $wallResult['error'];

                    return $result;
                }
            }

            if ($isRemoveAfterUpload
                && file_exists($filePath)
            ) {
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
}
