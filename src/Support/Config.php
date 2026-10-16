<?php

namespace App\Support;

class Config
{
    public const ENV_PATH = __DIR__ . '/../../.env';

    public const API_VERSION                 = '5.131';
    public const URL_WALL_POST               = 'https://api.vk.ru/method/wall.post?';
    public const URL_PHOTOS_GET              = 'https://api.vk.ru/method/photos.get?';
    public const URL_GET_PHOTO_UPLOAD_SERVER = 'https://api.vk.ru/method/photos.getUploadServer?';
    public const URL_PHOTOS_SAVE             = 'https://api.vk.ru/method/photos.save?';
    public const URL_VIDEOS_SAVE             = 'https://api.vk.ru/method/video.save?';
    public const URL_FAVE_GET_PHOTOS         = 'https://api.vk.ru/method/fave.getPhotos?';
    public const URL_LIKES_DELETE            = 'https://api.vk.ru/method/likes.delete';
    public const URL_FAVE_GET                = 'https://api.vk.ru/method/fave.get?';
    public const FAVE_COUNT                  = 50;
    public const FAVE_OFFSET                 = 0;
    public const SLEEP_TIME                  = '120000';

    public const FILES_FOLDER      = 'files';
    public const CAPTCHAS_FOLDER   = 'files/captchas';
    public const CAPTCHA_TEMP_NAME = 'temp.jpg';
    public const GITKEEP_FILE      = '.gitkeep';

    public const UPLOAD_IMAGES_FOLDER      = self::FILES_FOLDER . '/uploadImages';
    public const UPLOAD_VIDEOS_FOLDER      = self::FILES_FOLDER . '/uploadVideos';
    public const VIDEO_THUMBS_FOLDER       = self::FILES_FOLDER . '/videoThumbs';
    public const VIDEO_THUMBS_MAX_PARALLEL = 10;
    public const DONE_VIDEOS_FOLDER        = '/var/www/doneVideos';
    public const DONE_IMAGES_FOLDER        = '/var/www/doneImages';

    // -6 - album ID for Profile Avatar Photos
    // -7 - album ID for Wall Photos
    // -15 - album ID for Saved Photos

    public const ERROR_CODE_CAPTCHA = 14;

    public static function loadEnv(): void
    {
        $lines = file(self::ENV_PATH, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key           = trim($key);
            $value         = trim($value);

            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}
