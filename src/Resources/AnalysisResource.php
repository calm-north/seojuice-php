<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\AnalysisStatus;
use SEOJuice\Exceptions\SEOJuiceException;
use SEOJuice\HttpClient;

final class AnalysisResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    public function submit(string $url): AnalysisStatus
    {
        $data = $this->http->post("websites/{$this->domain}/analysis/", [
            'url' => $url,
        ]);

        return AnalysisStatus::fromArray($data);
    }

    public function status(string $analysisId): AnalysisStatus
    {
        $data = $this->http->get("websites/{$this->domain}/analysis/{$analysisId}/");

        return AnalysisStatus::fromArray($data);
    }

    public function waitForCompletion(
        string $analysisId,
        int $pollInterval = 3,
        int $maxAttempts = 20,
    ): AnalysisStatus {
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $result = $this->status($analysisId);

            if ($result->isComplete() || $result->isFailed()) {
                return $result;
            }

            sleep($pollInterval);
        }

        throw new SEOJuiceException(
            "Analysis {$analysisId} did not complete within " . ($maxAttempts * $pollInterval) . " seconds",
            'timeout',
        );
    }
}
