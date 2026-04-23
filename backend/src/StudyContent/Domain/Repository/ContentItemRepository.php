<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Repository;

use App\StudyContent\Domain\Model\ContentItem\ContentItem;
use App\StudyContent\Domain\Model\ContentItem\ContentItemId;

interface ContentItemRepository
{
    public function save(ContentItem $contentItem): void;

    public function get(ContentItemId $id): ContentItem;

    public function find(ContentItemId $id): ?ContentItem;

    /**
     * @param list<ContentItemId> $ids
     * @return list<ContentItem>
     */
    public function getByIds(array $ids): array;
}
