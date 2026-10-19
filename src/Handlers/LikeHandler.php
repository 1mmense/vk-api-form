<?php

namespace App\Handlers;

use App\Support\Config;

class LikeHandler
{
    private VkApiService $vkApi;

    public function __construct(VkApiService $vkApi)
    {
        $this->vkApi = $vkApi;
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

        $this->vkApi->postData[CURLOPT_URL] = $url_get_fave_photos;
        $this->vkApi->postData[CURLOPT_POSTFIELDS] = [
            'access_token' => $this->vkApi->postFields['access_token'],
            'v'            => $this->vkApi->postFields['v'],
        ];

        $result = $this->vkApi->executePostQuery();

        $items = $result['response']['items'] ?? null;

        if (!empty($items)) {
            foreach ($items as $item_key => $item) {
                usort($items[$item_key]['sizes'], function ($a, $b) {
                    return $a['width'] < $b['width'];
                });

                $largestSize = reset($items[$item_key]['sizes']);

                unset($items[$item_key]['sizes']);

                $items[$item_key]['url'] = $largestSize['url'];
            }
        } else {
            fn_plog($result);
        }

        return $items;
    }

    public function downloadLikedPhotos($items = [])
    {
        $is_success = true;

        $this->vkApi->postData[CURLOPT_URL] = Config::URL_LIKES_DELETE;

        $postFields = [
            'access_token' => $this->vkApi->postFields['access_token'],
            'v'            => $this->vkApi->postFields['v'],
            'type'         => 'photo',
        ];

        foreach ($items as $item_key => $item) {
            $basename_full = basename($item['url']);
            $basename = substr($basename_full, 0, strpos($basename_full, '?')) ?? $basename_full;

            $this->vkApi->postData[CURLOPT_POSTFIELDS] = array_merge($postFields, [
                'owner_id' => $item['owner_id'],
                'item_id'  => $item['id'],
            ]);

            $response = $this->vkApi->executePostQuery();

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