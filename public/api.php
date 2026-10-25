<?php

declare(strict_types=1);

use App\Classes\VkApiService;
use App\Controllers\ImagesController;
use App\Controllers\VideosController;
use App\Handlers\ImageHandler;
use App\Handlers\VideoHandler;
use App\Routing\Router;
use App\Support\Config;
use App\Support\Env;
use App\Support\FileStorage;

require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json; charset=utf-8');

try {
    Env::load(Config::envPath());

    // $vkApi = new VkApiService(
    //     token: $_ENV['TOKEN'],
    //     ownerId: (int) $_ENV['OWNER_ID']
    // );

    $vkApi = new VkApiService();

    $fileStorage = new FileStorage(
        filesDir: Config::basePath() . '/' . Config::FILES_FOLDER,
        urlPrefix: Config::FILES_PREFIX
    );

    $imageHandler = new ImageHandler(vkApi: $vkApi, fileStorage: $fileStorage);
    $videoHandler = new VideoHandler(vkApi: $vkApi, fileStorage: $fileStorage);

    $imagesController = new ImagesController(imageHandler: $imageHandler, fileStorage: $fileStorage);
    $videosController = new VideosController(videoHandler: $videoHandler, fileStorage: $fileStorage);

    $router = new Router(imagesController: $imagesController, videosController: $videosController);

    $router->dispatch(
        httpRequestMethod: $_SERVER['REQUEST_METHOD'],
        mode: $_GET['mode'] ?? null
    );
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
