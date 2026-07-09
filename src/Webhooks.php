<?php

declare(strict_types=1);

namespace SEOJuice;

final class Webhooks
{
    /**
     * Verify an HMAC-SHA256 webhook signature against the raw request body.
     *
     * $secret is used as-is (UTF-8) as the HMAC key. $signature is the raw hex
     * digest from the X-SEOJuice-Signature header. Constant-time comparison.
     *
     * Fails closed: an empty $secret (e.g. an unset env var) always returns
     * false, so a missing secret can never accept a forged signature.
     */
    public static function verifySignature(string $secret, string $body, string $signature): bool
    {
        if ($secret === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $body, $secret);

        return hash_equals($expected, $signature);
    }
}
