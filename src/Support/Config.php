<?php

declare(strict_types=1);

namespace App\Support;

class Config
{
    public const ENV_FILE_NAME = '.env';

    public const REQUEST_SLEEP_TIME_MS        = 2200;
    public const API_VERSION                  = '5.131';
    public const URL_WALL_POST                = 'https://api.vk.ru/method/wall.post?';
    public const URL_PHOTOS_GET               = 'https://api.vk.ru/method/photos.get?';
    public const URL_GET_IMAGES_UPLOAD_SERVER = 'https://api.vk.ru/method/photos.getUploadServer?';
    public const URL_PHOTOS_SAVE              = 'https://api.vk.ru/method/photos.save?';
    public const URL_VIDEOS_SAVE              = 'https://api.vk.ru/method/video.save?';

    public const FILES_FOLDER    = 'public/files';
    public const FILES_PREFIX    = 'files';
    public const GITKEEP_FILE    = '.gitkeep';
    public const THUMB_EXTENSION = '.webp';

    public const UPLOAD_IMAGES_FOLDER      = 'uploadImages';
    public const UPLOAD_VIDEOS_FOLDER      = 'uploadVideos';
    public const VIDEO_THUMBS_FOLDER       = 'videoThumbs';
    public const VIDEO_THUMBS_MAX_PARALLEL = 10;
    public const DONE_VIDEOS_FOLDER        = '/var/www/doneVideos';
    public const DONE_IMAGES_FOLDER        = '/var/www/doneImages';

    // -6 - album ID for Profile Avatar Photos
    // -7 - album ID for Wall Photos
    // -15 - album ID for Saved Photos

    public const ERROR_CODE_CAPTCHA = 14;

    public static function basePath(): string
    {
        return dirname(__DIR__, 2);
    }

    public static function envPath(): string
    {
        return self::basePath() . '/' . self::ENV_FILE_NAME;
    }
}
