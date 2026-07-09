<?php

declare(strict_types=1);

namespace SEOJuice\Exceptions;

final class RateLimitException extends SEOJuiceException
{
    public readonly ?int $retryAfter;

    public function __construct(string $message, string $errorCode = 'unknown', ?int $retryAfter = null)
    {
        parent::__construct($message, $errorCode);
        $this->retryAfter = $retryAfter;
    }
}
