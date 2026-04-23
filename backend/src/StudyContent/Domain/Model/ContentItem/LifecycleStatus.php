<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

use function in_array;

enum LifecycleStatus: string
{
    case DRAFT = 'draft';
    case IMPORTED = 'imported';
    case PROCESSED = 'processed';
    case IN_REVIEW = 'in_review';
    case APPROVED = 'approved';
    case PUBLISHED = 'published';
    case DEPRECATED = 'deprecated';
    case ARCHIVED = 'archived';
    case BLOCKED = 'blocked';

    public function isTerminalLike(): bool
    {
        return in_array($this, [self::ARCHIVED, self::BLOCKED], true);
    }
}
