<?php

declare(strict_types=1);

namespace SEOJuice\Injection;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use SEOJuice\Config;
use SEOJuice\Exceptions\SEOJuiceException;

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

    public function suggestions(string $url): Suggestions
    {
        try {
            $response = $this->client->request('GET', 'suggestions', [
                'query' => ['url' => $url],
            ]);

            $body = (string) $response->getBody();
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

            return Suggestions::fromArray($data);
        } catch (GuzzleException $e) {
            throw new SEOJuiceException(
                'Failed to fetch suggestions: ' . $e->getMessage(),
                'smart_client_error',
            );
        }
    }
}
