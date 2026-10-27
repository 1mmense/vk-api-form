<?php

declare(strict_types=1);

namespace App\Exceptions;

class RoutingException extends \RuntimeException
{
    public function __construct(
        string $message,
        private int $httpStatusCode
    ) {
        parent::__construct($message, $httpStatusCode);
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public static function methodNotAllowed(string $method): self
    {
        return new self("Method '{$method}' is not allowed", 405);
    }

    public static function modeIsRequired(): self
    {
        return new self('mode is required', 400);
    }

    public static function routeNotFound(string $method, string $mode): self
    {
        return new self("Route '{$method}/{$mode}' not found", 404);
    }
}
