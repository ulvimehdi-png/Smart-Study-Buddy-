<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

enum ReviewOutcome: string
{
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CHANGES_REQUESTED = 'changes_requested';
    case BLOCKED = 'blocked';
}
