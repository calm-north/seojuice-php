<?php

declare(strict_types=1);

namespace SEOJuice\Tests;

use PHPUnit\Framework\TestCase;
use SEOJuice\Webhooks;

final class WebhooksTest extends TestCase
{
    public function testValidSignaturePasses(): void
    {
        $secret = 'whsec_test_secret';
        $body = '{"event":"change.created","change":{"id":1}}';
        $signature = hash_hmac('sha256', $body, $secret);

        $this->assertTrue(Webhooks::verifySignature($secret, $body, $signature));
    }

    public function testTamperedBodyFails(): void
    {
        $secret = 'whsec_test_secret';
        $body = '{"event":"change.created","change":{"id":1}}';
        $signature = hash_hmac('sha256', $body, $secret);

        $tamperedBody = '{"event":"change.created","change":{"id":2}}';

        $this->assertFalse(Webhooks::verifySignature($secret, $tamperedBody, $signature));
    }

    public function testWrongSecretFails(): void
    {
        $body = '{"event":"change.created","change":{"id":1}}';
        $signature = hash_hmac('sha256', $body, 'whsec_correct_secret');

        $this->assertFalse(Webhooks::verifySignature('whsec_wrong_secret', $body, $signature));
    }

    public function testEmptySignatureReturnsFalseWithoutError(): void
    {
        $secret = 'whsec_test_secret';
        $body = '{"event":"change.created","change":{"id":1}}';

        $this->assertFalse(Webhooks::verifySignature($secret, $body, ''));
    }

    public function testShortSignatureReturnsFalseWithoutError(): void
    {
        $secret = 'whsec_test_secret';
        $body = '{"event":"change.created","change":{"id":1}}';

        $this->assertFalse(Webhooks::verifySignature($secret, $body, 'abc123'));
    }

    public function testEmptySecretRejectsForgedSignature(): void
    {
        $body = '{"event":"change.created","change":{"id":1}}';
        // An attacker who knows the body forges an HMAC using the empty key.
        $forged = hash_hmac('sha256', $body, '');

        $this->assertFalse(Webhooks::verifySignature('', $body, $forged));
    }

    public function testValidSignatureWithRealSecretStillVerifies(): void
    {
        $secret = 'whsec_test_secret';
        $body = '{"event":"change.created","change":{"id":1}}';
        $signature = hash_hmac('sha256', $body, $secret);

        $this->assertTrue(Webhooks::verifySignature($secret, $body, $signature));
    }
}
