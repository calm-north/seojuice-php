<?php

declare(strict_types=1);

namespace SEOJuice\Enums;

enum ChangeStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Applied = 'applied';
    case Pulled = 'pulled';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Reverted = 'reverted';
    case Expired = 'expired';
}
