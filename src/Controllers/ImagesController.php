<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Handlers\ImageHandler;
use App\Support\Response;

class ImagesController
{
    public function __construct(
        private ImageHandler $imageHandler
    ) {
    }

    public function index(): array
    {
        return Response::success($this->imageHandler->getImages());
    }

    public function upload(): array
    {
        $params = [
            'filePath'           => filter_input(INPUT_POST, 'filePath'),
            'isPostImagesToWall' => filter_input(INPUT_POST, 'isPostImagesToWall', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'isMoveAfterUpload'  => filter_input(INPUT_POST, 'isMoveImagesAfterUpload', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
        ];

        if (empty($params['filePath'])) {
            return Response::error("No 'filePath' specified");
        }

        $uploadResult = $this->imageHandler->uploadImage(params: $params);

        if (!empty($uploadResult['success'])) {
            return Response::success($uploadResult);
        }

        return Response::error($uploadResult['error'] ?? 'Unknown upload error');
    }
}