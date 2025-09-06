<?php

require_once '../helpers/Service.php';

class VkApi
{
    // IF IT DOESN'T WORK, CHANGE SLEEP_TIME TO A LARGER VALUE (300000)
    // https://oauth.vk.ru/authorize?client_id=6385226&redirect_uri=https://oauth.vk.ru/blank.html&display=page&scope=offline,photos,video,wall,groups,friends&response_type=token&v=5.131
    // public const TOKEN = 'vk1.a.B_1TTdy0nUP1-zuYZwpwJ7oUMKYws9fvkznhSvviaz4-6kCycpM3ibg36h6hfCHzNpoawzyn01zBNz4WFZTg9VJhOwrIjBv3Zq0UyxWG_h5_iCYLMS2YeptSUqE07pL5BhzQ0T0DGzm7DG0SlIkQg7ncc_aFlJ4gmnFefgDPg0vxrytxW6-L-vq2YMvk1uevV_XhG23ckAXJwSJSGE56fg';
    public const TOKEN = 'vk1.a.bBO4bZN574nyexHOJ_whCPItEPIsiDVmpCViYU14Hn_BiPB1anxHieyP06-UnZrI3nLse9WHPURnKWaMeozvG5aKRaX_rdhzEmVh8SqW3iWXv942CSrRxAoWYysViF-KiyU_S_fF49ibYo9U6ineD_G9hKJWHzyOuP4ruJzCpSoK3F_hzotnfRPUASn5XEhddz-q3S8Fu1cBA-04pbUHGQ';
    public const OWNER_ID = '-141375384';
    public const API_VERSION = '5.131';
    public const URL_WALL_POST = 'https://api.vk.ru/method/wall.post?';
    public const URL_PHOTOS_GET = 'https://api.vk.ru/method/photos.get?';
    public const URL_GET_PHOTO_UPLOAD_SERVER = 'https://api.vk.ru/method/photos.getUploadServer?';
    public const URL_PHOTOS_SAVE = 'https://api.vk.ru/method/photos.save?';
    public const URL_VIDEOS_SAVE = 'https://api.vk.ru/method/video.save?';
    public const URL_FAVE_GET_PHOTOS = 'https://api.vk.ru/method/fave.getPhotos?';
    public const URL_LIKES_DELETE = 'https://api.vk.ru/method/likes.delete';
    public const URL_FAVE_GET = 'https://api.vk.ru/method/fave.get?';
    public const FAVE_COUNT = 50;
    public const FAVE_OFFSET = 0;
    // public const SLEEP_TIME = '10000001';
    // public const SLEEP_TIME = '8000001';
    public const SLEEP_TIME = '120000';

    public const UPLOAD_IMAGES_FOLDER = FILES_FOLDER . '/uploadImages';
    public const UPLOAD_VIDEOS_FOLDER = FILES_FOLDER . '/uploadVideos';
    public const DONE_VIDEOS_FOLDER = FILES_FOLDER . '/doneVideos';
    public const DONE_IMAGES_FOLDER = 'I:/Games/DARK SOULS REMASTERED/_TPUP/Assassin/Yiff-Flash Collection';

    // -6 - album ID for Profile Avatar Photos
    // -7 - album ID for Wall Photos
    // -15 - album ID for Saved Photos

    public const ERROR_CODE_CAPTCHA = 14;
    public const GROUP_ID = '141375384';
    public const ALBUM_ID = '241471540';

    public $post_data = [];

    public $post_fields = [
        'owner_id'     => self::OWNER_ID,
        'from_group'   => true,
        'access_token' => self::TOKEN,
        'v'            => self::API_VERSION,
        'signed'       => 1,
    ];

    public function __construct()
    {
        $this->post_data = [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SAFE_UPLOAD    => true,
        ];
    }

    public function executePostQuery()
    {
        $result = false;

        if (empty($this->post_data)) {
            return $result;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $this->post_data);
        $query = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($query, true);

        usleep(self::SLEEP_TIME);

        return $result;
    }

    public function post($extra_post_fields)
    {
        $result = false;

        if (empty($extra_post_fields)) {
            return $result;
        }

        $this->post_data[CURLOPT_URL] = self::URL_WALL_POST;
        $this->post_data[CURLOPT_POSTFIELDS] = array_merge($this->post_fields, $extra_post_fields);

        $result = $this->executePostQuery();

        return $result;
    }

    public function getAlbumPhotos($extra_post_fields)
    {
        $result = [];

        if (empty($extra_post_fields)) {
            return $result;
        }

        $this->post_data[CURLOPT_URL] = self::URL_PHOTOS_GET;
        $this->post_data[CURLOPT_POSTFIELDS] = array_merge($this->post_fields, $extra_post_fields);

        $result = $this->executePostQuery();

        return $result;
    }

    public function getPhotosUrls($photos)
    {
        $result = [];

        if (empty($photos)) {
            return $result;
        }

        foreach ($photos as $key => $photo) {
            usort($photos[$key]['sizes'], function ($a, $b) {
                return $a['width'] < $b['width'];
            });

            $largest_size = reset($photos[$key]['sizes']);
            unset($photos[$key]['sizes']);
            $photos[$key]['url'] = $largest_size['url'];
        }

        $result = $photos;

        return $result;
    }

    public function savePhotoToAlbum($extra_post_fields)
    {
        $result = false;

        $default_fields = [
            'group_id' => self::GROUP_ID,
            'album_id' => self::ALBUM_ID,
        ];

        $extra_post_fields = array_merge($default_fields, $extra_post_fields);

        if (empty($extra_post_fields['file_path'])) {
            $files = $this->getFilesFromFolder();
        } else {
            $files = [$extra_post_fields['file_path']];
        }

        if (empty($extra_post_fields) || empty($files)) {
            return $result;
        }

        $get_upload_server_fields = array_merge($this->post_fields, [
            'group_id' => $extra_post_fields['group_id'],
            'album_id' => $extra_post_fields['album_id'],
        ]);

        $this->post_data[CURLOPT_URL] = self::URL_GET_PHOTO_UPLOAD_SERVER;
        $this->post_data[CURLOPT_POSTFIELDS] = $get_upload_server_fields;
        $get_upload_url_result = $this->executePostQuery();
        $upload_url = $get_upload_url_result['response']['upload_url'] ?? null;

        if (empty($upload_url)) {
            $result = false;

            return $result;
        }

        foreach ($files as $file_key => $file_path) {
            $this->post_data[CURLOPT_URL] = $upload_url;
            $this->post_data[CURLOPT_HEADER] = false;
            $pathinfo = pathinfo($file_path);

            if (empty($file_path) || !file_exists($file_path)) {
                $result = false;

                return $result;
            }

            $this->post_data[CURLOPT_POSTFIELDS] = [
                'file1' => new CURLFile(
                    $file_path,
                    mime_content_type($file_path),
                    $pathinfo['basename']
                ),
            ];

            $upload_result = $this->executePostQuery();
            $server = $upload_result['server'] ?? null;
            $photos_list = $upload_result['photos_list'] ?? null;
            $album_id = $upload_result['aid'] ?? null;
            $hash = $upload_result['hash'] ?? null;

            if (!isset($server, $photos_list, $album_id, $hash)) {
                $result = false;

                return $result;
            }

            $this->post_data[CURLOPT_URL] = self::URL_PHOTOS_SAVE;
            $this->post_data[CURLOPT_POSTFIELDS] = [
                'access_token' => $this->post_fields['access_token'],
                'v'            => $this->post_fields['v'],
                'group_id'     => $extra_post_fields['group_id'],
                'server'       => $server,
                'photos_list'  => $photos_list,
                'album_id'     => $album_id,
                'hash'         => $hash,
            ];

            $save_result = $this->executePostQuery();
            $saved_photo_data = reset($save_result['response']);

            $fields = [
                'attachments' => 'photo-' . VkApi::GROUP_ID . '_' . $saved_photo_data['id'],
            ];

            $result = $this->post($fields);

            rename(
                $file_path,
                str_replace(self::UPLOAD_IMAGES_FOLDER, self::DONE_IMAGES_FOLDER, $file_path)
            );
        }
    }

    public function saveVideoToAlbum()
    {
        $result = false;
        $files = $this->getFilesFromFolder(false);

        if (empty($files)) {
            return $result;
        }

        $fields = [
            'group_id' => self::GROUP_ID,
            'album_id' => self::ALBUM_ID,
        ];

        foreach ($files as $file_key => $file_path) {
            $pathinfo = pathinfo($file_path);

            $this->post_data[CURLOPT_POSTFIELDS] = array_merge($this->post_fields, [
                'group_id'    => $fields['group_id'],
                'repeat'      => 1,
                'no_comments' => 0,
                'name'        => $pathinfo['basename'],
            ]);

            $this->post_data[CURLOPT_URL] = self::URL_VIDEOS_SAVE;
            $get_upload_url_result = $this->executePostQuery();
            $upload_url = $get_upload_url_result['response']['upload_url'] ?? null;

            $this->post_data[CURLOPT_URL] = $upload_url;

            $this->post_data[CURLOPT_POSTFIELDS] = [
                'video_file' => new CURLFile(
                    $file_path,
                    mime_content_type($file_path),
                    $pathinfo['basename']
                ),
            ];

            $save_result = $this->executePostQuery();

            rename(
                $file_path,
                str_replace(self::UPLOAD_VIDEOS_FOLDER, self::DONE_VIDEOS_FOLDER, $file_path)
            );

            // Uncomment to post each video to the wall
            // if (!empty($save_result['video_id'])) {
            //     $fields = [
            //         'attachments' => 'video-' . self::GROUP_ID . '_' . $save_result['video_id'],
            //     ];

            //     $result = $this->post($fields);
            // }
        }
    }

    public function getFilesFromFolder($is_photos = true, $is_for_src = false)
    {
        $files = [];

        $folder = $is_photos ? self::UPLOAD_IMAGES_FOLDER : self::UPLOAD_VIDEOS_FOLDER;

        if (!is_dir($folder)) {
            return $files;
        }

        $dir_content = array_diff(scandir($folder), ['..', '.', GITKEEP_FILE]);
        $files_array = array_values($dir_content);

        foreach ($files_array as $file_key => $file) {
            if ($is_for_src) {
                $files[$file_key] = str_replace('../', '', $folder) . '/' . $file;
            } else {
                $files[$file_key] = $folder . '/' . $file;
            }
        }

        return $files;
    }

    public function getFavePhotos($params = [])
    {
        $params = array_merge([
            'count'  => self::FAVE_COUNT,
            'offset' => self::FAVE_OFFSET,
        ], $params);

        // $params['count'] = 1;
        // $params['offset'] = 100;

        $url_get_fave_photos = self::URL_FAVE_GET_PHOTOS;

        if (!empty($params['count'])) {
            $url_get_fave_photos .= 'count=' . $params['count'];
        }

        if (!empty($params['offset'])) {
            $url_get_fave_photos .= '&offset=' . $params['offset'];
        }

        $this->post_data[CURLOPT_URL] = $url_get_fave_photos;
        $this->post_data[CURLOPT_POSTFIELDS] = [
            'access_token' => $this->post_fields['access_token'],
            'v'            => $this->post_fields['v'],
        ];

        $result = $this->executePostQuery();

        $items = $result['response']['items'] ?? null;

        if (!empty($items)) {
            foreach ($items as $item_key => $item) {
                usort($items[$item_key]['sizes'], function ($a, $b) {
                    return $a['width'] < $b['width'];
                });

                $largest_size = reset($items[$item_key]['sizes']);

                unset($items[$item_key]['sizes']);

                $items[$item_key]['url'] = $largest_size['url'];
            }
        } else {
            fn_plog($result);
        }

        return $items;
    }

    public function downloadLikedPhotos($items = [])
    {
        $is_success = true;

        $this->post_data[CURLOPT_URL] = self::URL_LIKES_DELETE;

        $post_fields = [
            'access_token' => $this->post_fields['access_token'],
            'v'            => $this->post_fields['v'],
            'type'         => 'photo',
        ];

        foreach ($items as $item_key => $item) {
            $basename_full = basename($item['url']);
            $basename = substr($basename_full, 0, strpos($basename_full, '?'));

            $this->post_data[CURLOPT_POSTFIELDS] = array_merge($post_fields, [
                'owner_id' => $item['owner_id'],
                'item_id'  => $item['id'],
            ]);

            $response = $this->executePostQuery();

            if (empty($response['error']['error_msg'])) {
                $ch = curl_init($item['url']);
                $fp = fopen(self::UPLOAD_IMAGES_FOLDER . '/' . $basename, 'wb');
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);

                echo 'Processed photo "' . $basename . '"' . '</br>';
            } else {
                $is_success = false;
                echo $response['error']['error_msg'] . '</br>';

                break;
            }
        }

        return $is_success;
    }

    public function iterate($params = [])
    {
        $params = array_merge([
            'offset' => self::FAVE_OFFSET,
        ], $params);

        $items = $this->getFavePhotos($params);

        $is_sleep = false;

        // return $items;

        if (!empty($items)) {
            $is_success = $this->downloadLikedPhotos($items);

            if ($is_success) {
                $params['offset'] += self::FAVE_COUNT;
                $this->iterate($params);
            } else {
                // $is_sleep = true;

                // usleep(120000000);
                $this->iterate($params);
            }
        } else {
            // usleep(20000000);
            // $this->iterate($params);
        }
    }

    public function getItems($params = [])
    {
        $params = array_merge([
            'offset' => self::FAVE_OFFSET,
        ], $params);

        $items = $this->getFavePhotos($params);

        return $items;
    }
}
