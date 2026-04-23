<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\Collection;

enum CollectionStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
