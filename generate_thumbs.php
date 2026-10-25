<?php

declare(strict_types=1);

use App\Support\Config;
use App\Support\Env;
use App\Support\FileStorage;

require_once __DIR__ . '/vendor/autoload.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $lockFile = __DIR__ . '/thumbs.lock';
    $lockFp   = fopen($lockFile, 'c');

    if (!flock($lockFp, LOCK_EX | LOCK_NB)) {
        exit(0);
    }

    Env::load(Config::envPath());

    $fileStorage = new FileStorage(
        filesDir: Config::basePath() . '/' . Config::FILES_FOLDER,
        urlPrefix: Config::FILES_PREFIX
    );

    $videos = $fileStorage->listFiles(Config::UPLOAD_VIDEOS_FOLDER);

    $maxParallel = Config::VIDEO_THUMBS_MAX_PARALLEL;
    $processes   = [];
    $running     = 0;

    foreach ($videos as $videoPath) {
        $pathinfo  = pathinfo($videoPath);
        $thumbPath = $fileStorage->getThumbFile($pathinfo['filename']);

        if (file_exists($thumbPath)) {
            continue;
        }

        $cmd = sprintf(
            '/usr/bin/ffmpeg -i %s -vf "thumbnail=300,scale=240:-1" -frames:v 1 -c:v libwebp -quality 60 -y %s > /dev/null 2>&1',
            escapeshellarg($videoPath),
            escapeshellarg($thumbPath)
        );

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $processes[$videoPath] = [
            'process' => proc_open($cmd, $descriptors, $pipes),
            'pipes'   => $pipes,
            'thumb'   => $thumbPath,
        ];

        $running++;

        while ($running >= $maxParallel) {
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

    flock($lockFp, LOCK_UN);
    fclose($lockFp);

    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
