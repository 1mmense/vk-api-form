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
            'get_all_images'   => [ImagesController::class, 'index'],
            'get_all_videos'   => [VideosController::class, 'index'],
            'get_video_thumbs' => [VideosController::class, 'thumbs'],
        ],
        'POST' => [
            'upload_images' => [ImagesController::class, 'upload'],
            'upload_videos' => [VideosController::class, 'upload'],
        ],
    ];

    public function __construct(
        private ImagesController $imagesController,
        private VideosController $videosController,
    ) {
    }

    /**
     * @throws RoutingException
     */
    public function dispatch(string $httpRequestMethod, ?string $mode): array
    {
        if (!isset($this->routes[$httpRequestMethod])) {
            throw RoutingException::methodNotAllowed($httpRequestMethod);
        }

        if ($mode === null) {
            throw RoutingException::modeIsRequired();
        }

        $route = $this->routes[$httpRequestMethod][$mode] ?? null;

        if ($route === null) {
            throw RoutingException::routeNotFound($httpRequestMethod, $mode);
        }

        [$controllerClass, $methodName] = $route;

        $controller = match ($controllerClass) {
            ImagesController::class => $this->imagesController,
            VideosController::class => $this->videosController,
        };

        return $controller->$methodName();
    }
}