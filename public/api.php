<?php

ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../vendor/autoload.php';

use App\Support\Config;
use App\Controllers\UploadController;

Config::loadEnv();

try {
    $controller = new UploadController();
    $controller->handleRequest();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
