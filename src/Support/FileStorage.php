<?php

declare(strict_types=1);

namespace App\Support;

class FileStorage
{
    /**
     * @param string $filesDir  Абсолютный путь к корню хранилища
     * @param string $urlPrefix Веб-префикс для генерации URL
     */
    public function __construct(
        private string $filesDir,
        private string $urlPrefix,
    ) {
    }

    /**
     * Возвращает абсолютные пути к файлам в указанной подпапке.
     *
     * @param string $subdir Подпапка относительно корня хранилища
     *
     * @return list<string> Абсолютные пути к файлам
     */
    public function listFiles(string $subdir): array
    {
        $dir = $this->filesDir . '/' . $subdir;

        if (!is_dir($dir)) {
            return [];
        }

        $entries = scandir($dir);

        if ($entries === false) {
            return [];
        }

        return array_values(array_filter(
            array_map(
                static fn (string $file): string => $dir . '/' . $file,
                array_diff($entries, ['..', '.', Config::GITKEEP_FILE])
            ),
            static fn (string $path): bool => is_file($path)
        ));
    }

    /**
     * Возвращает веб-пути к файлам в указанной подпапке.
     *
     * @param string $subdir Подпапка относительно корня хранилища
     *
     * @return list<string> Веб-пути для фронтенда
     */
    public function listUrls(string $subdir): array
    {
        return array_map(
            fn (string $path): string => $this->urlPrefix . '/' . $subdir . '/' . basename($path),
            $this->listFiles($subdir)
        );
    }

    public function getThumbFile(string $videoName = ''): string
    {
        $result = '';

        if (empty($videoName)) {
            return $result;
        }

        $result =
            Config::basePath()
            . '/'
            . Config::FILES_FOLDER
            . '/'
            . Config::VIDEO_THUMBS_FOLDER
            . '/thumbnail_'
            . $videoName
            . Config::THUMB_EXTENSION;

        return $result;
    }

    public function getThumbUrl(string $videoName = ''): string
    {
        $result = '';

        if (empty($videoName)) {
            return $result;
        }

        $result =
            Config::FILES_PREFIX
            . '/'
            . Config::VIDEO_THUMBS_FOLDER
            . '/thumbnail_'
            . $videoName
            . Config::THUMB_EXTENSION;

        return $result;
    }
}
