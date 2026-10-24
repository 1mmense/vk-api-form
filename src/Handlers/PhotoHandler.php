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

    public function uploadImage(array $params = []): array
    {
        $result = [
            'success' => false,
            'error'   => null,
        ];

        $isMoveAfterUpload = (bool) $params['isMoveAfterUpload'] ?? false;
        $isPostToWall      = (bool) $params['isPostImagesToWall'] ?? false;
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

        $getUploadServerFields = array_merge($this->vkApi->postFields, [
            'group_id' => $extraPostFields['group_id'],
            'album_id' => $extraPostFields['album_id'],
        ]);

        $this->vkApi->postData[CURLOPT_URL]        = Config::URL_GET_PHOTO_UPLOAD_SERVER;
        $this->vkApi->postData[CURLOPT_POSTFIELDS] = $getUploadServerFields;

        $getUploadUrlResult = $this->vkApi->executePostQuery();
        $uploadUrl          = $getUploadUrlResult['response']['upload_url'] ?? null;

        if (empty($uploadUrl)) {
            $result['error'] = 'upload_url is empty';

            return $result;
        }

        $pathinfo = pathinfo($filePath);

        $this->vkApi->postData[CURLOPT_URL]    = $uploadUrl;
        $this->vkApi->postData[CURLOPT_HEADER] = false;

        $this->vkApi->postData[CURLOPT_POSTFIELDS] = [
            'file1' => new \CURLFile(
                $filePath,
                mime_content_type($filePath),
                $pathinfo['basename']
            ),
        ];

        $uploadResult = $this->vkApi->executePostQuery();
        $server       = $uploadResult['server'] ?? null;
        $photosList   = $uploadResult['photos_list'] ?? null;
        $albumId      = $uploadResult['aid'] ?? null;
        $hash         = $uploadResult['hash'] ?? null;

        if (!isset($server, $photosList, $albumId, $hash)) {
            $result['error'] = 'upload_result missing fields';

            return $result;
        }

        $this->vkApi->postData[CURLOPT_URL] = Config::URL_PHOTOS_SAVE;

        $this->vkApi->postData[CURLOPT_POSTFIELDS] = [
            'access_token' => $this->vkApi->postFields['access_token'],
            'v'            => $this->vkApi->postFields['v'],
            'group_id'     => $extraPostFields['group_id'],
            'server'       => $server,
            'photos_list'  => $photosList,
            'album_id'     => $albumId,
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
