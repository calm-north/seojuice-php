<?php

declare(strict_types=1);

namespace SEOJuice\Exceptions;

/**
 * HTTP 402: the website is locked or needs a trial/card. The server sends
 * `checkout_url` and `lock_state` with the error.
 */
final class PaymentRequiredException extends SEOJuiceException
{
    public readonly ?string $checkoutUrl;
    public readonly ?string $lockState;

    public function __construct(
        string $message,
        string $errorCode = 'unknown',
        ?string $checkoutUrl = null,
        ?string $lockState = null,
    ) {
        parent::__construct($message, $errorCode);
        $this->checkoutUrl = $checkoutUrl;
        $this->lockState = $lockState;
    }
}
