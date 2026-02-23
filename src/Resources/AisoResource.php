<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\AisoData;
use SEOJuice\Enums\Period;
use SEOJuice\HttpClient;

final class AisoResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    public function get(
        Period $period = Period::ThirtyDays,
        bool $includeHistory = false,
    ): AisoData {
        $data = $this->http->get("websites/{$this->domain}/aiso/", [
            'period' => $period->value,
            'include_history' => $includeHistory ? 'true' : null,
        ]);

        return AisoData::fromArray($data);
    }
}
