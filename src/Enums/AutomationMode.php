<?php

declare(strict_types=1);

namespace SEOJuice\Enums;

enum AutomationMode: string
{
    case Off = 'off';
    case Suggest = 'suggest';
    case ManualDeploy = 'manual_deploy';
    case AutoDeploy = 'auto_deploy';
}
