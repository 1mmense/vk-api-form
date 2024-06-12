<?php

require_once 'VkApiClass.php';
require_once 'ServiceFunctions.php';

$vkApi = new VkApi();

$photos = $vkApi->getFilesFromFolder();

$offset = $_REQUEST['offset'];
$file = $photos[$offset] ?? null;

if (empty($file)) {
    return;
}

$response = [
    'file_path' => $file,
];

echo json_encode($response);
