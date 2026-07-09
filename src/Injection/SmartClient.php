<?php

declare(strict_types=1);

namespace SEOJuice\Injection;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use SEOJuice\Config;

final class SmartClient
{
    private readonly Client $client;
    private readonly ?LoggerInterface $logger;

    public function __construct(Config $config, ?Client $guzzleClient = null, ?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->client = $guzzleClient ?? new Client([
            'base_uri' => $config->smartUrl . '/',
            'timeout' => $config->timeout,
            'connect_timeout' => 10,
            'headers' => [
                'User-Agent' => $config->userAgent,
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function suggestions(string $url): array
    {
        try {
            $response = $this->client->request('GET', 'suggestions', [
                'query' => ['url' => $url],
            ]);

            $body = (string) $response->getBody();
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

            return is_array($data) ? $data : [];
        } catch (\Throwable $e) {
            $this->logger?->warning('[seojuice] suggestions() failed, returning []', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
