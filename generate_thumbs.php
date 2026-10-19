<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Support\Config;
use App\Handlers\Handler;
use App\Handlers\VideoHandler;
use App\Classes\VkApiService;

$lock_file = __DIR__ . '/thumbs.lock';
$lock_fp   = fopen($lock_file, 'c');

if (!flock($lock_fp, LOCK_EX | LOCK_NB)) {
    exit(0);
}

$max_parallel = Config::VIDEO_THUMBS_MAX_PARALLEL;

$vkApi        = new VkApiService();
$handler      = new Handler();
$videoHandler = new VideoHandler($vkApi);

$videos = $handler->getFilesFromFolder(false, true);

$processes = [];
$running   = 0;

foreach ($videos as $videoPath) {
    $pathinfo   = pathinfo($videoPath);
    $thumb_path = Config::VIDEO_THUMBS_FOLDER . '/thumbnail_' . $pathinfo['filename'] . '.webp';

    if (file_exists($thumb_path)) {
        continue;
    }

    $cmd = sprintf(
        '/usr/bin/ffmpeg -i %s -vf "thumbnail=300,scale=240:-1" -frames:v 1 -c:v libwebp -quality 60 -y %s > /dev/null 2>&1',
        escapeshellarg($videoPath),
        escapeshellarg($thumb_path)
    );

    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $processes[$videoPath] = [
        'process' => proc_open($cmd, $descriptors, $pipes),
        'pipes'   => $pipes,
        'thumb'   => $thumb_path,
    ];

    $running++;

    while ($running >= $max_parallel) {
        foreach ($processes as $path => $data) {
            $status = proc_get_status($data['process']);

            if (!$status['running']) {
                fclose($data['pipes'][1]);
                fclose($data['pipes'][2]);
                proc_close($data['process']);

                unset($processes[$path]);

                $running--;

                break;
            }
        }

        usleep(100000);
    }
}

// Дожидаемся оставшихся
foreach ($processes as $path => $data) {
    fclose($data['pipes'][1]);
    fclose($data['pipes'][2]);
    proc_close($data['process']);
}

flock($lock_fp, LOCK_UN);
fclose($lock_fp);
