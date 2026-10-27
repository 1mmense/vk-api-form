<?php

declare(strict_types=1);

namespace App\Classes;

use App\Support\Config;

class VkApiService
{
    /**
     * Базовые поля, которые VK API ожидает в каждом вызове метода
     * (`owner_id`, `from_group`, `access_token`, `v`, `signed`).
     *
     * @var array<string, mixed>
     */
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
     * Выполняет вызов метода VK API.
     *
     * К переданным полям автоматически добавляются базовые поля (`owner_id`, `from_group`, `access_token`, `v`, `signed`).
     *
     * @param string               $methodUrl  Полный URL метода VK API
     * @param array<string, mixed> $postFields Поля конкретного метода
     *
     * @return array<string, mixed> Ответ API в виде массива или `['error' => string]` при сбое
     */
    public function callApiMethod(string $methodUrl, array $postFields): array
    {
        $fields = array_merge($this->basePostFields, $postFields);

        return $this->sendRequest($methodUrl, $fields);
    }

    /**
     * Загружает файл по временной ссылке, которую вернул VK API.
     *
     * Такой запрос принимает только сам файл и не требует базовых полей VK API.
     * Отправка `owner_id`, `access_token` и т. п. на этот адрес приводит к ошибке HTTP 415.
     *
     * @param string     $uploadUrl Одноразовый URL для загрузки, полученный из ответа VK API
     * @param \CURLFile  $file      Файл для загрузки
     *
     * @return array<string, mixed> Ответ сервера загрузки в виде массива или `['error' => string]` при сбое
     */
    public function uploadToTemporaryUrl(string $uploadUrl, \CURLFile $file): array
    {
        return $this->sendRequest($uploadUrl, ['file' => $file]);
    }

    /**
     * Публикует запись на стене сообщества.
     *
     * @param array<string, mixed> $extraPostFields Поля метода wall.post (например, `attachments`)
     *
     * @return array<string, mixed> Ответ API в виде массива или `['error' => string]` при сбое
     */
    public function postToWall(array $extraPostFields): array
    {
        return $this->callApiMethod(
            methodUrl: Config::URL_WALL_POST,
            postFields: $extraPostFields,
        );
    }

    /**
     * Отправляет POST-запрос и возвращает JSON-ответ в виде массива либо строку с ошибкой.
     *
     * @param string               $url    Полный URL запроса
     * @param array<string, mixed> $fields Поля для CURLOPT_POSTFIELDS
     *
     * @return array<string, mixed> Декодированный ответ или `['error' => string]` при сбое
     */
    private function sendRequest(string $url, array $fields): array
    {
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

        $httpCode    = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        $contentType = (string) curl_getinfo($curlHandle, CURLINFO_CONTENT_TYPE);

        $result = json_decode($query, true);

        if (!is_array($result)) {
            return [
                'error' => sprintf(
                    'VK API returned non-JSON response (HTTP %d, %s): %s',
                    $httpCode,
                    $contentType ?: 'unknown content-type',
                    mb_substr(trim((string) $query), 0, 300)
                ),
            ];
        }

        return $result;
    }
}
