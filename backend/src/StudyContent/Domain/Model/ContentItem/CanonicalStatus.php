<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

enum CanonicalStatus: string
{
    case NONE = 'none';
    case CANONICAL = 'canonical';
    case DUPLICATE_OF_CANONICAL = 'duplicate_of_canonical';
}
