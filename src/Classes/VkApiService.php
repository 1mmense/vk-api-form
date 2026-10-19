<?php

namespace App\Classes;

use App\Support\Config;

class VkApiService
{
    public $postData = [];
    public $postFields = [];

    public function __construct()
    {
        $this->postFields = [
            'owner_id'     => $_ENV['OWNER_ID'],
            'from_group'   => true,
            'access_token' => $_ENV['TOKEN'],
            'v'            => Config::API_VERSION,
            'signed'       => 1,
        ];

        $this->postData = [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];
    }

    public function executePostQuery()
    {
        $result = false;

        if (empty($this->postData)) {
            return $result;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $this->postData);
        $query = curl_exec($ch);
        $result = json_decode($query, true);

        usleep(Config::SLEEP_TIME);

        return $result;
    }

    public function postToWall($extraPostFields)
    {
        $result = false;

        if (empty($extraPostFields)) {
            return $result;
        }

        $this->postData[CURLOPT_URL] = Config::URL_WALL_POST;
        $this->postData[CURLOPT_POSTFIELDS] = array_merge($this->postFields, $extraPostFields);

        $result = $this->executePostQuery();

        return $result;
    }

    public function getAlbumPhotos($extraPostFields)
    {
        $result = [];

        if (empty($extraPostFields)) {
            return $result;
        }

        $this->postData[CURLOPT_URL] = Config::URL_PHOTOS_GET;
        $this->postData[CURLOPT_POSTFIELDS] = array_merge($this->postFields, $extraPostFields);

        $result = $this->executePostQuery();

        return $result;
    }
}