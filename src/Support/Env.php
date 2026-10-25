<?php

declare(strict_types=1);

namespace App\Support;

class Env
{
    public static function load(string $path): void
    {
        if (!is_file($path)) {
            throw new \RuntimeException("Env file not found: {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value]    = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}
