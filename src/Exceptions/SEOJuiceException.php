<?php

declare(strict_types=1);

namespace SEOJuice\Exceptions;

use RuntimeException;

class SEOJuiceException extends RuntimeException
{
    public readonly string $errorCode;

    public function __construct(string $message, string $errorCode = 'unknown')
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
    }
}
