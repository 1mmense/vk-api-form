<?php

require_once '../classes/VkApiClass.php';

$mode = $_REQUEST['mode'] ?? null;

$vkApi = new VkApi();

if ($mode === 'get_files_from_folder') {
    $photos = $vkApi->getFilesFromFolder(true, true);

    $offset = $_REQUEST['offset'];
    $file = $photos[$offset] ?? null;

    if (empty($file)) {
        return;
    }

    $response = [
        'file_path' => $file,
    ];

    echo json_encode($response);
} elseif ($mode === 'save_photos') {
    $fields = [];

    if (!empty($_REQUEST['file_path'])) {
        $fields['file_path'] = '../' . $_REQUEST['file_path'];
    }

    if (!empty($_REQUEST['is_post_all_photos'])
        && ($_REQUEST['is_post_all_photos'] === true || $_REQUEST['is_post_all_photos'] === 'true')
    ) {
        unset($fields['file_path']);
    }

    $vkApi->savePhotoToAlbum($fields);
} elseif ($mode === 'save_videos') {
    $vkApi->saveVideoToAlbum();
}
