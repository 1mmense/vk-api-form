<?php

namespace App\Handlers;

use App\Support\Config;
use App\Classes\VkApiService;

class PhotoHandler extends Handler
{
    protected VkApiService $vk_api;

    public function __construct(VkApiService $vk_api)
    {
        $this->vk_api = $vk_api;
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

            $largest_size = reset($photos[$key]['sizes']);
            unset($photos[$key]['sizes']);
            $photos[$key]['url'] = $largest_size['url'];
        }

        $result = $photos;

        return $result;
    }

    public function savePhotoToAlbum(array $extra_post_fields): array
    {
        $result = [
            'success' => false,
            'error'   => null,
        ];

        $default_fields = [
            'group_id' => $_ENV['GROUP_ID'],
            'album_id' => $_ENV['ALBUM_ID'],
        ];

        $extra_post_fields = array_merge($default_fields, $extra_post_fields);

        if (empty($extra_post_fields['file_path'])) {
            $files = $this->getFilesFromFolder();
        } else {
            $files = [$extra_post_fields['file_path']];
        }

        if (empty($extra_post_fields) || empty($files)) {
            return $result;
        }

        $get_upload_server_fields = array_merge($this->vk_api->post_fields, [
            'group_id' => $extra_post_fields['group_id'],
            'album_id' => $extra_post_fields['album_id'],
        ]);

        $this->vk_api->post_data[CURLOPT_URL]        = Config::URL_GET_PHOTO_UPLOAD_SERVER;
        $this->vk_api->post_data[CURLOPT_POSTFIELDS] = $get_upload_server_fields;

        $get_upload_url_result = $this->vk_api->executePostQuery();
        $upload_url            = $get_upload_url_result['response']['upload_url'] ?? null;

        if (empty($upload_url)) {
            $result['error'] = 'upload_url is empty';

            return $result;
        }

        foreach ($files as $file_key => $file_path) {
            $this->vk_api->post_data[CURLOPT_URL]    = $upload_url;
            $this->vk_api->post_data[CURLOPT_HEADER] = false;

            $pathinfo = pathinfo($file_path);

            if (empty($file_path) || !file_exists($file_path)) {
                $result['error'] = 'file not found: ' . $file_path;

                return $result;
            }

            $this->vk_api->post_data[CURLOPT_POSTFIELDS] = [
                'file1' => new \CURLFile(
                    $file_path,
                    mime_content_type($file_path),
                    $pathinfo['basename']
                ),
            ];

            $upload_result = $this->vk_api->executePostQuery();
            $server        = $upload_result['server'] ?? null;
            $photos_list   = $upload_result['photos_list'] ?? null;
            $album_id      = $upload_result['aid'] ?? null;
            $hash          = $upload_result['hash'] ?? null;

            if (!isset($server, $photos_list, $album_id, $hash)) {
                $result['error'] = 'upload_result missing fields';

                return $result;
            }

            $this->vk_api->post_data[CURLOPT_URL] = Config::URL_PHOTOS_SAVE;

            $this->vk_api->post_data[CURLOPT_POSTFIELDS] = [
                'access_token' => $this->vk_api->post_fields['access_token'],
                'v'            => $this->vk_api->post_fields['v'],
                'group_id'     => $extra_post_fields['group_id'],
                'server'       => $server,
                'photos_list'  => $photos_list,
                'album_id'     => $album_id,
                'hash'         => $hash,
            ];

            $save_result = $this->vk_api->executePostQuery();

            if (!empty($save_result['error'])) {
                $result['error'] = $save_result['error'];

                return $result;
            }

            $saved_photo_data = reset($save_result['response']);

            if (empty($saved_photo_data['id'])) {
                $result['error'] = 'saved photo id is empty';

                return $result;
            }

            $fields = [
                'attachments' => 'photo-' . $_ENV['GROUP_ID'] . '_' . $saved_photo_data['id'],
            ];

            $wall_result = $this->vk_api->post($fields);

            if (!empty($wall_result['error'])) {
                $result['error'] = $wall_result['error'];

                return $result;
            }

            if (file_exists($file_path)) {
                $destination = Config::DONE_IMAGES_FOLDER . '/' . $pathinfo['basename'];

                if (copy($file_path, $destination)
                    && file_exists($destination)
                ) {
                    unlink($file_path);
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
