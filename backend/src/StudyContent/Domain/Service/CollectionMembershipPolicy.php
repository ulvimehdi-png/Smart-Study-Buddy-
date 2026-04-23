<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Service;

use App\StudyContent\Domain\Model\Collection\Collection;
use App\StudyContent\Domain\Model\ContentItem\ContentItem;

interface CollectionMembershipPolicy
{
    public function assertCanBeAdded(
        Collection  $collection,
        ContentItem $contentItem,
    ): void;

    public function assertCollectionCanBePublished(
        Collection $collection,
    ): void;
}
