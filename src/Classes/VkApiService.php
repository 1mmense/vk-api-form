<?php

declare(strict_types=1);

namespace App\Classes;

use App\Support\Config;

class VkApiService
{
    private array $basePostFields;

    public function __construct(private VkConfig $vkConfig)
    {
        $this->basePostFields = [
            'owner_id'     => $this->vkConfig->ownerId,
            'from_group'   => true,
            'access_token' => $this->vkConfig->accessToken,
            'v'            => Config::API_VERSION,
            'signed'       => 1,
        ];
    }

    /**
     * Выполняет POST-запрос к указанному URL.
     *
     * @param string $url        Полный URL метода VK API
     * @param array  $postFields Поля для отправки (будут объединены с базовыми)
     *
     * @return array Ответ API в виде массива или ['error' => ...] при сбое
     */
    public function executePostQuery(string $url, array $postFields): array
    {
        $fields = array_merge($this->basePostFields, $postFields);

        $curlHandle = curl_init();

        if ($curlHandle === false) {
            return ['error' => 'Failed to initialize cURL'];
        }

        curl_setopt_array($curlHandle, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $fields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $query = curl_exec($curlHandle);

        if ($query === false) {
            $error = curl_error($curlHandle);

            return ['error' => 'cURL error: ' . $error];
        }

        $result = json_decode($query, true);

        if (!is_array($result)) {
            return ['error' => 'Invalid JSON response from VK API'];
        }

        return $result;
    }

    public function postToWall(array $extraPostFields): array
    {
        return $this->executePostQuery(
            url: Config::URL_WALL_POST,
            postFields: $extraPostFields,
        );
    }
}
