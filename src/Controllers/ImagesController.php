<?php

namespace App\Controllers;

use App\Classes\VkApiService;
use App\Handlers\Handler;
use App\Handlers\PhotoHandler;
use App\Support\Config;

class ImagesController
{
    private VkApiService $vkApi;
    private Handler $handler;
    private PhotoHandler $photoHandler;

    public function __construct()
    {
        $this->vkApi        = new VkApiService();
        $this->handler      = new Handler($this->vkApi);
        $this->photoHandler = new PhotoHandler($this->vkApi);
    }

    public function index(): void
    {
        $photos = $this->handler->getFilesFromFolder(true, true);

        $result = [];

        foreach ($photos as $photoPath) {
            $result[] = [
                'path'     => $photoPath,
                'basename' => basename($photoPath),
            ];
        }

        echo json_encode($result);
    }

    public function upload(): void
    {
        $params = [
            'filePath'           => filter_input(INPUT_POST, 'filePath'),
            'isPostImagesToWall' => filter_input(INPUT_POST, 'isPostImagesToWall', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'isMoveAfterUpload'  => filter_input(INPUT_POST, 'isMoveImagesAfterUpload', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
        ];

        if (empty($params['filePath'])) {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'error'   => 'No filePath specified',
            ]);

            return;
        }

        $result = $this->photoHandler->uploadImage(params: $params);

        if ($result['success']) {
            echo json_encode([
                'success' => true,
            ]);

            return;
        }

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'error'   => $result['error'],
        ]);
    }
}
