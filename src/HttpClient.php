<?php

declare(strict_types=1);

namespace SEOJuice;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use SEOJuice\Exceptions\AuthException;
use SEOJuice\Exceptions\ForbiddenException;
use SEOJuice\Exceptions\NotFoundException;
use SEOJuice\Exceptions\RateLimitException;
use SEOJuice\Exceptions\SEOJuiceException;
use SEOJuice\Exceptions\ServerException;
use SEOJuice\Exceptions\ValidationException;

class HttpClient
{
    private readonly Client $client;
    private readonly Config $config;

    public function __construct(
        private readonly string $apiKey,
        Config $config,
        ?Client $guzzleClient = null,
    ) {
        $this->config = $config;
        $this->client = $guzzleClient ?? new Client([
            'timeout' => $config->timeout,
            'headers' => [
                'User-Agent' => $config->userAgent,
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    public function get(string $path, array $query = []): array
    {
        $url = $this->buildUrl($path, $query);

        try {
            $response = $this->client->request('GET', $url);
            $body = (string) $response->getBody();

            return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        } catch (GuzzleException $e) {
            throw new SEOJuiceException($e->getMessage(), 'network_error');
        }
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    public function post(string $path, array $body = []): array
    {
        $url = $this->buildUrl($path, []);

        try {
            $response = $this->client->request('POST', $url, [
                'json' => $body,
            ]);
            $responseBody = (string) $response->getBody();

            return json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        } catch (GuzzleException $e) {
            throw new SEOJuiceException($e->getMessage(), 'network_error');
        }
    }

    /**
     * @param array<string, mixed> $query
     */
    public function getRaw(string $path, array $query = []): string
    {
        $url = $this->buildUrl($path, $query);

        try {
            $response = $this->client->request('GET', $url);

            return (string) $response->getBody();
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        } catch (GuzzleException $e) {
            throw new SEOJuiceException($e->getMessage(), 'network_error');
        }
    }

    /**
     * @param array<string, mixed> $query
     */
    private function buildUrl(string $path, array $query): string
    {
        $url = $this->config->baseUrl . '/' . ltrim($path, '/');

        $filtered = array_filter($query, fn ($value) => $value !== null);

        if ($filtered !== []) {
            $url .= '?' . http_build_query($filtered);
        }

        return $url;
    }

    /**
     * @return never
     */
    private function handleRequestException(RequestException $e): never
    {
        $status = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
        $body = $e->hasResponse() ? (string) $e->getResponse()->getBody() : '';

        $decoded = [];
        if ($body !== '') {
            $decoded = json_decode($body, true) ?? [];
        }

        $message = $decoded['detail'] ?? $decoded['message'] ?? $e->getMessage();
        $errorCode = $decoded['error_code'] ?? 'unknown';

        $this->throwForStatus($status, $message, $errorCode);
    }

    /**
     * @return never
     */
    private function throwForStatus(int $status, string $message, string $errorCode): never
    {
        throw match (true) {
            $status === 401 => new AuthException($message, $errorCode),
            $status === 403 => new ForbiddenException($message, $errorCode),
            $status === 404 => new NotFoundException($message, $errorCode),
            $status === 429 => new RateLimitException($message, $errorCode),
            $status === 400, $status === 422 => new ValidationException($message, $errorCode),
            $status >= 500 => new ServerException($message, $errorCode),
            default => new SEOJuiceException($message, $errorCode),
        };
    }
}
