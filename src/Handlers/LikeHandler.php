<?php

namespace App\Handlers;

use App\Support\Config;

class LikeHandler
{
    private VkApiService $vk_api;

    public function __construct(VkApiService $vk_api)
    {
        $this->vk_api = $vk_api;
    }

    public function getFavePhotos($params = [])
    {
        $params = array_merge([
            'count'  => Config::FAVE_COUNT,
            'offset' => Config::FAVE_OFFSET,
        ], $params);

        // $params['count'] = 1;
        // $params['offset'] = 100;

        $url_get_fave_photos = Config::URL_FAVE_GET_PHOTOS;

        if (!empty($params['count'])) {
            $url_get_fave_photos .= 'count=' . $params['count'];
        }

        if (!empty($params['offset'])) {
            $url_get_fave_photos .= '&offset=' . $params['offset'];
        }

        $this->vk_api->post_data[CURLOPT_URL] = $url_get_fave_photos;
        $this->vk_api->post_data[CURLOPT_POSTFIELDS] = [
            'access_token' => $this->vk_api->post_fields['access_token'],
            'v'            => $this->vk_api->post_fields['v'],
        ];

        $result = $this->vk_api->executePostQuery();

        $items = $result['response']['items'] ?? null;

        if (!empty($items)) {
            foreach ($items as $item_key => $item) {
                usort($items[$item_key]['sizes'], function ($a, $b) {
                    return $a['width'] < $b['width'];
                });

                $largest_size = reset($items[$item_key]['sizes']);

                unset($items[$item_key]['sizes']);

                $items[$item_key]['url'] = $largest_size['url'];
            }
        } else {
            fn_plog($result);
        }

        return $items;
    }

    public function downloadLikedPhotos($items = [])
    {
        $is_success = true;

        $this->vk_api->post_data[CURLOPT_URL] = Config::URL_LIKES_DELETE;

        $post_fields = [
            'access_token' => $this->vk_api->post_fields['access_token'],
            'v'            => $this->vk_api->post_fields['v'],
            'type'         => 'photo',
        ];

        foreach ($items as $item_key => $item) {
            $basename_full = basename($item['url']);
            $basename = substr($basename_full, 0, strpos($basename_full, '?')) ?? $basename_full;

            $this->vk_api->post_data[CURLOPT_POSTFIELDS] = array_merge($post_fields, [
                'owner_id' => $item['owner_id'],
                'item_id'  => $item['id'],
            ]);

            $response = $this->vk_api->executePostQuery();

            if (empty($response['error']['error_msg'])) {
                $ch = curl_init($item['url']);
                $fp = fopen(Config::UPLOAD_IMAGES_FOLDER . '/' . $basename, 'wb');
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);

                echo 'Processed photo "' . $basename . '"' . '</br>';
            } else {
                $is_success = false;
                echo $response['error']['error_msg'] . '</br>';

                break;
            }
        }

        return $is_success;
    }

    public function iterate($params = [])
    {
        $params = array_merge([
            'offset' => Config::FAVE_OFFSET,
        ], $params);

        $items = $this->getFavePhotos($params);

        $is_sleep = false;

        // return $items;

        if (!empty($items)) {
            $is_success = $this->downloadLikedPhotos($items);

            if ($is_success) {
                $params['offset'] += Config::FAVE_COUNT;
                $this->iterate($params);
            } else {
                // $is_sleep = true;

                // usleep(120000000);
                $this->iterate($params);
            }
        } else {
            // usleep(20000000);
            // $this->iterate($params);
        }
    }

    public function getItems($params = [])
    {
        $params = array_merge([
            'offset' => Config::FAVE_OFFSET,
        ], $params);

        $items = $this->getFavePhotos($params);

        return $items;
    }
}