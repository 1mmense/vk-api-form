<?php

define('FILES_FOLDER', '../files');
define('CAPTCHAS_FOLDER', FILES_FOLDER . '/captchas');
define('CAPTCHA_TEMP_NAME', 'temp.jpg');
define('GITKEEP_FILE', '.gitkeep');

function fn_plog()
{
    $file = 'plog.log';

    $resource = fopen($file, 'a+');

    if ($resource) {
        $date = new DateTime('now', new DateTimeZone('Europe/Samara'));

        $content = PHP_EOL . print_r(
            array_merge(
                [
                    'time' => $date->format('d M Y, h:i:s'),
                ],
                func_get_args()
            ),
            true
        );

        fwrite($resource, $content);
    }

    fclose($resource);
}

function fn_print_r()
{
    $args = func_get_args();

    $console = false;
    if (defined('CONSOLE') || empty($_SERVER['REQUEST_METHOD']) || defined('API')) {
        $console = true;
    }

    if ($console) {
        echo(PHP_EOL);
        foreach ($args as $v) {
            echo(print_r($v, true) . PHP_EOL);
        }
        echo(PHP_EOL);
    } else {
        echo('<ol style="font-family: Courier; font-size: 12px; border: 1px solid #dedede; background-color: #efefef; float: left; padding-right: 20px;">');
        foreach ($args as $v) {
            echo('<li><pre>' . htmlspecialchars(print_r($v, true)) . "\n" . '</pre></li>');
        }
        echo('</ol><div style="clear:left;"></div>');
    }
}

function fn_print_die()
{
    $args = func_get_args();
    call_user_func_array('fn_print_r', $args);
    exit(1);
}

function fn_echo_breaker($text)
{
    echo $text . "\n";
}

// function toBool($value)
// {
//     return $value === true || $value === self::YES;
// }