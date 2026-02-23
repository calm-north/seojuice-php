<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class BacklinkDomain
{
    public readonly int $id;
    public readonly string $domain;
    public readonly ?int $rank;
    public readonly ?float $spamScore;
    public readonly ?string $country;
    public readonly ?string $platform;
    public readonly ?string $tld;

    public function __construct(
        int $id,
        string $domain,
        ?int $rank,
        ?float $spamScore,
        ?string $country,
        ?string $platform,
        ?string $tld,
    ) {
        $this->id = $id;
        $this->domain = $domain;
        $this->rank = $rank;
        $this->spamScore = $spamScore;
        $this->country = $country;
        $this->platform = $platform;
        $this->tld = $tld;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            domain: (string) $data['domain'],
            rank: isset($data['rank']) ? (int) $data['rank'] : null,
            spamScore: isset($data['spam_score']) ? (float) $data['spam_score'] : null,
            country: $data['country'] ?? null,
            platform: $data['platform'] ?? null,
            tld: $data['tld'] ?? null,
        );
    }
}
