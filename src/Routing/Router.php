<?php

declare(strict_types=1);

namespace App\Routing;

use App\Controllers\ImagesController;
use App\Controllers\VideosController;

class Router
{
    public function __construct(
        private ImagesController $imagesController,
        private VideosController $videosController,
    ) {
    }

    // Карта: HTTP-метод => mode => [Контроллер, Метод]
    private array $routes = [
        'GET' => [
            'get_all_images'   => [ImagesController::class, 'index'],
            'get_all_videos'   => [VideosController::class, 'index'],
            'get_video_thumbs' => [VideosController::class, 'thumbs'],
        ],
        'POST' => [
            'upload_images' => [ImagesController::class, 'upload'],
            'upload_videos' => [VideosController::class, 'upload'],
        ],
    ];

    public function dispatch(string $httpRequestMethod, ?string $mode): void
    {
        if (!isset($this->routes[$httpRequestMethod])) {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['error' => "Method '{$httpRequestMethod}' is not allowed"]);

            return;
        }

        if ($mode === null) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'mode is required']);

            return;
        }

        $route = $this->routes[$httpRequestMethod][$mode] ?? null;

        if ($route === null) {
            http_response_code(404); // Not Found
            echo json_encode(['error' => "Route '{$httpRequestMethod}/{$mode}' not found"]);

            return;
        }

        [$controllerClass, $methodName] = $route;

        $controller = match ($controllerClass) {
            ImagesController::class => $this->imagesController,
            VideosController::class => $this->videosController,
        };

        $controller->$methodName();
    }
}
