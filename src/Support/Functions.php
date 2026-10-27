<?php

declare(strict_types=1);

namespace App\Support;

class Functions
{
    public static function fn_plog()
    {
        $file     = __DIR__ . '/../../plog.log';
        $resource = fopen($file, 'a+');

        if (!$resource) {
            return;
        }

        $date = new \DateTime('now', new \DateTimeZone('Europe/Samara'));

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
        fclose($resource);
    }

    public static function fn_print_r()
    {
        header('Content-Type: text/html; charset=utf-8');

        $args = func_get_args();

        $console = false;

        if (empty($_SERVER['REQUEST_METHOD'])) {
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

        exit;
    }

    public static function fn_echo_breaker($text)
    {
        echo $text . "\n";
    }
}
