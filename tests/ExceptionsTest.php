<?php

declare(strict_types=1);

namespace SEOJuice\Tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use SEOJuice\Exceptions\AuthException;
use SEOJuice\Exceptions\ForbiddenException;
use SEOJuice\Exceptions\NotFoundException;
use SEOJuice\Exceptions\RateLimitException;
use SEOJuice\Exceptions\SEOJuiceException;
use SEOJuice\Exceptions\ServerException;
use SEOJuice\Exceptions\ValidationException;

final class ExceptionsTest extends TestCase
{
    public function testSeoJuiceExceptionStoresErrorCode(): void
    {
        $exception = new SEOJuiceException('Something went wrong', 'custom_error');

        $this->assertSame('custom_error', $exception->errorCode);
        $this->assertSame('Something went wrong', $exception->getMessage());
    }

    public function testSeoJuiceExceptionDefaultsToUnknownErrorCode(): void
    {
        $exception = new SEOJuiceException('Error occurred');

        $this->assertSame('unknown', $exception->errorCode);
    }

    public function testSeoJuiceExceptionExtendsRuntimeException(): void
    {
        $exception = new SEOJuiceException('test');

        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testAuthExceptionExtendsSeoJuiceException(): void
    {
        $exception = new AuthException('Unauthorized', 'auth_failed');

        $this->assertInstanceOf(SEOJuiceException::class, $exception);
        $this->assertSame('Unauthorized', $exception->getMessage());
        $this->assertSame('auth_failed', $exception->errorCode);
    }

    public function testForbiddenExceptionExtendsSeoJuiceException(): void
    {
        $exception = new ForbiddenException('Forbidden', 'forbidden');

        $this->assertInstanceOf(SEOJuiceException::class, $exception);
        $this->assertSame('Forbidden', $exception->getMessage());
        $this->assertSame('forbidden', $exception->errorCode);
    }

    public function testNotFoundExceptionExtendsSeoJuiceException(): void
    {
        $exception = new NotFoundException('Not found', 'not_found');

        $this->assertInstanceOf(SEOJuiceException::class, $exception);
        $this->assertSame('Not found', $exception->getMessage());
        $this->assertSame('not_found', $exception->errorCode);
    }

    public function testRateLimitExceptionExtendsSeoJuiceException(): void
    {
        $exception = new RateLimitException('Rate limited', 'rate_limit');

        $this->assertInstanceOf(SEOJuiceException::class, $exception);
        $this->assertSame('Rate limited', $exception->getMessage());
        $this->assertSame('rate_limit', $exception->errorCode);
    }

    public function testValidationExceptionExtendsSeoJuiceException(): void
    {
        $exception = new ValidationException('Invalid input', 'validation_error');

        $this->assertInstanceOf(SEOJuiceException::class, $exception);
        $this->assertSame('Invalid input', $exception->getMessage());
        $this->assertSame('validation_error', $exception->errorCode);
    }

    public function testServerExceptionExtendsSeoJuiceException(): void
    {
        $exception = new ServerException('Server error', 'server_error');

        $this->assertInstanceOf(SEOJuiceException::class, $exception);
        $this->assertSame('Server error', $exception->getMessage());
        $this->assertSame('server_error', $exception->errorCode);
    }

    public function testAuthExceptionCanBeCaughtAsSeoJuiceException(): void
    {
        $caught = false;

        try {
            throw new AuthException('auth error', 'auth');
        } catch (SEOJuiceException $e) {
            $caught = true;
            $this->assertSame('auth error', $e->getMessage());
        }

        $this->assertTrue($caught);
    }

    public function testForbiddenExceptionCanBeCaughtAsSeoJuiceException(): void
    {
        $caught = false;

        try {
            throw new ForbiddenException('forbidden', 'forbidden');
        } catch (SEOJuiceException $e) {
            $caught = true;
        }

        $this->assertTrue($caught);
    }

    public function testNotFoundExceptionCanBeCaughtAsSeoJuiceException(): void
    {
        $caught = false;

        try {
            throw new NotFoundException('not found', 'not_found');
        } catch (SEOJuiceException $e) {
            $caught = true;
        }

        $this->assertTrue($caught);
    }

    public function testRateLimitExceptionCanBeCaughtAsSeoJuiceException(): void
    {
        $caught = false;

        try {
            throw new RateLimitException('rate limited', 'rate_limit');
        } catch (SEOJuiceException $e) {
            $caught = true;
        }

        $this->assertTrue($caught);
    }

    public function testValidationExceptionCanBeCaughtAsSeoJuiceException(): void
    {
        $caught = false;

        try {
            throw new ValidationException('bad input', 'validation');
        } catch (SEOJuiceException $e) {
            $caught = true;
        }

        $this->assertTrue($caught);
    }

    public function testServerExceptionCanBeCaughtAsSeoJuiceException(): void
    {
        $caught = false;

        try {
            throw new ServerException('server down', 'server');
        } catch (SEOJuiceException $e) {
            $caught = true;
        }

        $this->assertTrue($caught);
    }

    public function testAllExceptionsCanBeCaughtAsRuntimeException(): void
    {
        $exceptions = [
            new AuthException('msg', 'code'),
            new ForbiddenException('msg', 'code'),
            new NotFoundException('msg', 'code'),
            new RateLimitException('msg', 'code'),
            new ValidationException('msg', 'code'),
            new ServerException('msg', 'code'),
            new SEOJuiceException('msg', 'code'),
        ];

        foreach ($exceptions as $exception) {
            $this->assertInstanceOf(RuntimeException::class, $exception);
        }
    }
}
