<?php

require_once 'VkApiClass.php';
require_once 'ServiceFunctions.php';

$fields = [
    'group_id' => VkApi::GROUP_ID,
    'album_id' => VkApi::ALBUM_ID,
];

$vkApi = new VkApi();

$vkApi->saveVideoToAlbum($fields);
