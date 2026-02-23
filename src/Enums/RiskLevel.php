<?php

declare(strict_types=1);

namespace SEOJuice\Enums;

enum RiskLevel: string
{
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';
    case Safe = 'safe';
}
