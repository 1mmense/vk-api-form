<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Handlers\VideoHandler;
use App\Support\Response;

class VideosController
{
    public function __construct(
        private VideoHandler $videoHandler,
    ) {
    }

    public function index(): array
    {
        $this->videoHandler->removeOrphanThumbs();
        $this->videoHandler->startThumbGeneration();

        return Response::success($this->videoHandler->getVideosWithThumbs());
    }

    public function upload(): array
    {
        $params = [
            'filePath'           => filter_input(INPUT_POST, 'filePath'),
            'isPostVideosToWall' => filter_input(INPUT_POST, 'isPostVideosToWall', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'isMoveAfterUpload'  => filter_input(INPUT_POST, 'isMoveVideosAfterUpload', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
        ];

        if (empty($params['filePath'])) {
            return Response::error("No 'filePath' specified");
        }

        $uploadResult = $this->videoHandler->uploadVideo(params: $params);

        if (!empty($uploadResult['success'])) {
            return Response::success($uploadResult);
        }

        return Response::error($uploadResult['error'] ?? 'Unknown upload error');
    }

    public function thumbs(): array
    {
        return Response::success($this->videoHandler->getVideosWithThumbs());
    }
}
