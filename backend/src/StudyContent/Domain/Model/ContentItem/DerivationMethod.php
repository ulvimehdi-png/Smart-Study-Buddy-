<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

enum DerivationMethod: string
{
    case MANUAL = 'manual';
    case EXTRACTED = 'extracted';
    case SUMMARIZED = 'summarized';
    case GENERATED = 'generated';
    case CURATED = 'curated';
}
