<?php
declare(strict_types=1);

namespace App\Routing;

use App\Controllers\ImagesController;
use App\Controllers\VideosController;

class Router
{
    // Карта: HTTP-метод => mode => [Контроллер, Метод]
    private array $routes = [
        'GET' => [
            'get_all_photos'   => [ImagesController::class, 'index'],
            'get_all_videos'   => [VideosController::class, 'index'],
            'get_video_thumbs' => [VideosController::class, 'thumbs'],
        ],
        'POST' => [
            'upload_images' => [ImagesController::class, 'upload'],
            'upload_videos' => [VideosController::class, 'upload'],
        ],
    ];

    public function dispatch(string $httpRequestMethod, null|string $mode): void
    {
        // 1. Проверяем, есть ли такой метод в карте (GET/POST)
        if (!isset($this->routes[$httpRequestMethod])) {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['error' => "Method '{$httpRequestMethod}' is not allowed"]);

            return;
        }

        if ($mode === null) {
            http_response_code(400);
            echo json_encode(['error' => 'mode is required']);
            return;
        }

        // 2. Ищем mode в карте для этого метода
        $route = $this->routes[$httpRequestMethod][$mode] ?? null;

        if ($route === null) {
            http_response_code(404);
            echo json_encode(['error' => "Route '{$httpRequestMethod}/{$mode}' not found"]);

            return;
        }

        // 3. Создаём контроллер и вызываем метод
        [$controllerClass, $methodName] = $route;

        // if (!class_exists($controllerClass)) {
        //     http_response_code(500);
        //     echo json_encode(['error' => "Class '{$controllerClass}' not found"]);

        //     return;
        // }

        // if (!method_exists($controllerClass, $methodName)) {
        //     http_response_code(500);
        //     echo json_encode(['error' => "Method '{$methodName}' not found in class '{$controllerClass}'"]);

        //     return;
        // }

        $controller = new $controllerClass();
        $controller->$methodName();
    }
}
