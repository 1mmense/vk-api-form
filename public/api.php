<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../vendor/autoload.php';

use App\Routing\Router;
use App\Support\Config;

try {
    Config::loadEnv();

    $router = new Router();

    $router->dispatch(
        httpRequestMethod: $_SERVER['REQUEST_METHOD'],
        mode: $_GET['mode'] ?? null
    );
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
