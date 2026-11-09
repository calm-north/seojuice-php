<?php

declare(strict_types=1);

namespace SEOJuice\Enums;

/**
 * Lifecycle values the server returns in `status` (Proposed ... Expired).
 * Pending/Applied/Pulled/Verified/Reverted are legacy names: the changes list
 * filter still accepts them and the stats `by_status` keys still use them,
 * but responses never carry them.
 *
 * Responses can carry values added after this SDK release; use tryFrom().
 */
enum ChangeStatus: string
{
    case Proposed = 'proposed';
    case Approved = 'approved';
    case Live = 'live';
    case RolledBack = 'rolled_back';
    case Rejected = 'rejected';
    case Superseded = 'superseded';
    case Stale = 'stale';
    case Expired = 'expired';

    case Pending = 'pending';
    case Applied = 'applied';
    case Pulled = 'pulled';
    case Verified = 'verified';
    case Reverted = 'reverted';
}
