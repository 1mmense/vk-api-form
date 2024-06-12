<?php

class VkApi
{
    // IF IT DOESN'T WORK, CHANGE SLEEP_TIME TO A LARGER VALUE (300000)
    // https://oauth.vk.com/authorize?client_id=6385226&redirect_uri=https://oauth.vk.com/blank.html&display=page&scope=offline,photos,video,wall,groups&response_type=token&v=5.131
    protected const TOKEN = 'vk1.a.B_1TTdy0nUP1-zuYZwpwJ7oUMKYws9fvkznhSvviaz4-6kCycpM3ibg36h6hfCHzNpoawzyn01zBNz4WFZTg9VJhOwrIjBv3Zq0UyxWG_h5_iCYLMS2YeptSUqE07pL5BhzQ0T0DGzm7DG0SlIkQg7ncc_aFlJ4gmnFefgDPg0vxrytxW6-L-vq2YMvk1uevV_XhG23ckAXJwSJSGE56fg';
    protected const OWNER_ID = '-141375384';
    protected const API_VERSION = '5.131';
    protected const URL_WALL_POST = 'https://api.vk.com/method/wall.post?';
    protected const URL_PHOTOS_GET = 'https://api.vk.com/method/photos.get?';
    protected const URL_GET_PHOTO_UPLOAD_SERVER = 'https://api.vk.com/method/photos.getUploadServer?';
    protected const URL_PHOTOS_SAVE = 'https://api.vk.com/method/photos.save?';
    protected const URL_VIDEOS_SAVE = 'https://api.vk.com/method/video.save?';
    protected const SLEEP_TIME = '300000';
    protected const UPLOAD_IMAGES_FOLDER = 'UploadImages';
    protected const UPLOAD_VIDEOS_FOLDER = 'UploadVideos';
    protected const GITKEEP_FILE = '.gitkeep';

    public const ERROR_CODE_CAPTCHA = 14;
    public const GROUP_ID = '141375384';
    public const ALBUM_ID = '241471540';

    protected $post_data = [];

    protected $post_fields = [
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

    protected function executePostQuery()
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
        }
    }

    public function saveVideoToAlbum($extra_post_fields)
    {
        $result = false;
        $files = $this->getFilesFromFolder(false);

        if (empty($files)) {
            return $result;
        }

        foreach ($files as $file_key => $file_path) {
            $pathinfo = pathinfo($file_path);

            $this->post_data[CURLOPT_POSTFIELDS] = array_merge($this->post_fields, [
                'group_id'    => $extra_post_fields['group_id'],
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

            // Uncomment to post each video on the wall
            // if (!empty($save_result['video_id'])) {
            //     $fields = [
            //         'attachments' => 'video-' . VkApi::GROUP_ID . '_' . $save_result['video_id'],
            //     ];

            //     $result = $this->post($fields);
            // }
        }
    }

    public function getFilesFromFolder($is_photos = true)
    {
        $folder = $is_photos ? self::UPLOAD_IMAGES_FOLDER : self::UPLOAD_VIDEOS_FOLDER;

        $dir_content = array_diff(scandir($folder), ['..', '.', self::GITKEEP_FILE]);
        $files_array = array_values($dir_content);

        foreach ($files_array as $file_key => $file) {
            $files[$file_key] = $folder . '/' . $file;
        }

        return $files;
    }
}
