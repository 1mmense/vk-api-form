<?php

require_once 'VkApiClass.php';
require_once 'ServiceFunctions.php';

$fields = [
    'group_id' => VkApi::GROUP_ID,
    'album_id' => VkApi::ALBUM_ID,
];

$vkApi = new VkApi();

if (!empty($_REQUEST['file_path'])) {
    $fields['file_path'] = $_REQUEST['file_path'];
}

if (!empty($_REQUEST['is_post_all_photos'])
    && ($_REQUEST['is_post_all_photos'] === true || $_REQUEST['is_post_all_photos'] === 'true')
) {
    unset($fields['file_path']);
}

$result = $vkApi->savePhotoToAlbum($fields);

// fn_plog($result);
