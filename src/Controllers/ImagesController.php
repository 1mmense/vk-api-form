<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Handlers\Handler;
use App\Handlers\ImageHandler;
use App\Support\Config;
use App\Support\FileStorage;

class ImagesController
{
    public function __construct(
        private ImageHandler $imageHandler,
        private FileStorage $fileStorage
    ) {
    }

    public function index(): void
    {
        $images = $this->fileStorage->listUrls(Config::UPLOAD_IMAGES_FOLDER);

        $result = [];

        foreach ($images as $imagePath) {
            $result[] = [
                'path'     => $imagePath,
                'basename' => basename($imagePath),
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

        $result = $this->imageHandler->uploadImage(params: $params);

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
