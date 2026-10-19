<?php

namespace App\Classes;

use App\Support\Config;

class VkApiService
{
    public $post_data = [];
    public $post_fields = [];

    public function __construct()
    {
        $this->post_fields = [
            'owner_id'     => $_ENV['OWNER_ID'],
            'from_group'   => true,
            'access_token' => $_ENV['TOKEN'],
            'v'            => Config::API_VERSION,
            'signed'       => 1,
        ];

        $this->post_data = [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];
    }

    public function executePostQuery()
    {
        $result = false;

        if (empty($this->post_data)) {
            return $result;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $this->post_data);
        $query = curl_exec($ch);
        $result = json_decode($query, true);

        usleep(Config::SLEEP_TIME);

        return $result;
    }

    public function postToWall($extra_post_fields)
    {
        $result = false;

        if (empty($extra_post_fields)) {
            return $result;
        }

        $this->post_data[CURLOPT_URL] = Config::URL_WALL_POST;
        $this->post_data[CURLOPT_POSTFIELDS] = array_merge($this->post_fields, $extra_post_fields);

        $result = $this->executePostQuery();

        return $result;
    }

    public function getAlbumPhotos($extra_post_fields)
    {
        $result = [];

        if (empty($extra_post_fields)) {
            return $result;
        }

        $this->post_data[CURLOPT_URL] = Config::URL_PHOTOS_GET;
        $this->post_data[CURLOPT_POSTFIELDS] = array_merge($this->post_fields, $extra_post_fields);

        $result = $this->executePostQuery();

        return $result;
    }
}