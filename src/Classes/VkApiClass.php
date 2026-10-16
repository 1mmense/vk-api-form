<?php

namespace App\Services;

class HttpClient
{
    public function post(array $curlOptions): array|false
    {
        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            return false;
        }

        return json_decode($response, true);
    }
}