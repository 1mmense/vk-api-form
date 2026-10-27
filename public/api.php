<?php

declare(strict_types=1);

use App\Classes\VkApiService;
use App\Classes\VkConfig;
use App\Controllers\ImagesController;
use App\Controllers\VideosController;
use App\Handlers\ImageHandler;
use App\Handlers\VideoHandler;
use App\Routing\Router;
use App\Exceptions\RoutingException;
use App\Support\Config;
use App\Support\Env;
use App\Support\FileStorage;
use App\Support\Response;

require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json; charset=utf-8');

try {
    Env::load(Config::envPath());

    if (!isset($_ENV['TOKEN'], $_ENV['OWNER_ID'], $_ENV['GROUP_ID'], $_ENV['ALBUM_ID'])) {
        throw new \RuntimeException('Missing required environment variables');
    }

    $vkConfig = new VkConfig(
        accessToken: $_ENV['TOKEN'],
        ownerId: (string) $_ENV['OWNER_ID'],
        groupId: $_ENV['GROUP_ID'],
        albumId: $_ENV['ALBUM_ID'],
    );

    $vkApi = new VkApiService(vkConfig: $vkConfig);

    $fileStorage = new FileStorage(
        filesDir: Config::basePath() . '/' . Config::FILES_FOLDER,
        urlPrefix: Config::FILES_PREFIX
    );

    $imageHandler = new ImageHandler(vkApi: $vkApi, fileStorage: $fileStorage, vkConfig: $vkConfig);
    $videoHandler = new VideoHandler(vkApi: $vkApi, fileStorage: $fileStorage, vkConfig: $vkConfig);

    $imagesController = new ImagesController(imageHandler: $imageHandler);
    $videosController = new VideosController(videoHandler: $videoHandler);

    $router = new Router(imagesController: $imagesController, videosController: $videosController);

    $result = $router->dispatch(
        httpRequestMethod: $_SERVER['REQUEST_METHOD'],
        mode: $_GET['mode'] ?? null
    );

    echo json_encode($result);
} catch (RoutingException $e) {
    http_response_code($e->getHttpStatusCode());
    echo json_encode(Response::error($e->getMessage()));
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(Response::error($e->getMessage()));
}
