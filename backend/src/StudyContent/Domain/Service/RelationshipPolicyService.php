<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Service;

use App\StudyContent\Domain\Model\ContentItem\ContentItem;
use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\StudyContent\Domain\Model\ContentItem\RelationshipType;

interface RelationshipPolicyService
{
    public function assertRelationshipAllowed(
        ContentItem      $source,
        RelationshipType $type,
        ContentItemId    $targetId,
    ): void;
}
