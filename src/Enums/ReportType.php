<?php

declare(strict_types=1);

namespace SEOJuice\Enums;

enum ReportType: string
{
    case ThisMonth = 'this_month';
    case LastMonth = 'last_month';
    case ThisWeek = 'this_week';
    case LastWeek = 'last_week';
}
