<?php

require_once '../classes/VkApiClass.php';

$mode = $_REQUEST['mode'] ?? null;

if ($mode === 'get_liked_photos') {
    if (!empty($_REQUEST['captcha_image'])
        && !empty($_REQUEST['captcha_key'])
    ) {
        $temp_filename = '../' . $_REQUEST['captcha_image'];
        $temp_filename = substr($temp_filename, 0, strpos($temp_filename, '?'));

        if (file_exists($temp_filename)) {
            file_put_contents(
                CAPTCHAS_FOLDER . '/' . $_REQUEST['captcha_key'] . '.jpg',
                file_get_contents(
                    $temp_filename
                )
            );

            unlink(CAPTCHAS_FOLDER . '/' . CAPTCHA_TEMP_NAME);
        }
    }

    $vkApi = new VkApi();

    $items = $vkApi->getItems();

    if (!empty($items)) {
        $is_success = true;

        $vkApi->post_data[CURLOPT_URL] = VkApi::URL_LIKES_DELETE;

        $post_fields = [
            'access_token' => $vkApi->post_fields['access_token'],
            'v'            => $vkApi->post_fields['v'],
            'type'         => 'photo',
        ];

        foreach ($items as $item_key => $item) {
            $basename_full = basename($item['url']);
            $basename = substr($basename_full, 0, strpos($basename_full, '?'));

            $vkApi->post_data[CURLOPT_POSTFIELDS] = array_merge($post_fields, [
                'owner_id' => $item['owner_id'],
                'item_id'  => $item['id'],
            ]);

            if (!empty($_REQUEST['captcha_sid'])
                && !empty($_REQUEST['captcha_key'])
            ) {
                $vkApi->post_data[CURLOPT_POSTFIELDS] = array_merge($vkApi->post_data[CURLOPT_POSTFIELDS], [
                    'captcha_sid' => $_REQUEST['captcha_sid'],
                    'captcha_key' => $_REQUEST['captcha_key'],
                ]);
            }

            $response = $vkApi->executePostQuery();

            if (empty($response['error']['error_msg'])) {
                $ch = curl_init($item['url']);
                $fp = fopen(VkApi::UPLOAD_IMAGES_FOLDER . '/' . $basename, 'wb');
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);

                echo 'Processed photo "' . $basename . '"' . '</br>';
            } else {
                $is_success = false;
                echo json_encode($response);

                break;
            }
        }
    } else {
        echo 'reboot';
    }
} elseif ($mode === 'save_captcha') {
    if (!empty($_REQUEST['image'])) {
        $filename = CAPTCHAS_FOLDER . '/' . CAPTCHA_TEMP_NAME;

        $dir_content = array_diff(scandir(CAPTCHAS_FOLDER), [
            '..',
            '.',
            GITKEEP_FILE,
            CAPTCHA_TEMP_NAME,
        ]);

        $files_array = array_values($dir_content);

        foreach ($files_array as $file_key => $file) {
            $saved_captchas[$file_key] = CAPTCHAS_FOLDER . '/' . $file;
        }

        foreach ($saved_captchas as $captcha_key => $captcha) {
            if (file_exists($filename)
                && file_exists($captcha)
                && file_get_contents($captcha) == file_get_contents($filename)
            ) {
                fn_plog(
                    basename($captcha),
                    basename($filename)
                );

                $result['decoded_result'] = pathinfo(basename($captcha), PATHINFO_FILENAME);
            }
        }

        if (file_exists($filename)) {
            unlink($filename);
        }

        $ch = curl_init($_REQUEST['image']);
        $fp = fopen($filename, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        $filename = str_replace('../', '', $filename);

        $result['src'] = $filename . '?' . time();

        echo json_encode($result);
    }
}
