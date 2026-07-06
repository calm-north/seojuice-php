<?php

declare(strict_types=1);

namespace SEOJuice\Injection;

use GuzzleHttp\Client;
use SEOJuice\Config;

final class SmartClient
{
    private readonly Client $client;

    public function __construct(Config $config, ?Client $guzzleClient = null)
    {
        $this->client = $guzzleClient ?? new Client([
            'base_uri' => $config->smartUrl . '/',
            'timeout' => $config->timeout,
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
        } catch (\Throwable) {
            return [];
        }
    }
}
