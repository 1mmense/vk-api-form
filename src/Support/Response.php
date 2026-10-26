<?php

declare(strict_types=1);

namespace App\Support;

class Response
{
    public static function success(array $data = []): array
    {
        return [
            'success' => true,
            'data'    => $data,
            'error'   => null,
        ];
    }

    public static function error(string $error): array
    {
        return [
            'success' => false,
            'data'    => null,
            'error'   => $error,
        ];
    }
}