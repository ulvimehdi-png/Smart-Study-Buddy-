<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Service;

use App\StudyContent\Domain\Model\ContentItem\ContentItem;
use App\StudyContent\Domain\Model\ContentItem\ContentItemId;

interface CanonicalizationService
{
    public function assertCanBecomeCanonical(
        ContentItem $contentItem,
    ): void;

    public function assignCanonicalRepresentative(
        ContentItem   $candidate,
        ContentItemId $canonicalId,
    ): void;
}
