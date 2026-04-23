<?php

declare(strict_types=1);

namespace App\Tests\StudyContent\Support\Double;

use App\StudyContent\Domain\Model\ContentItem\ContentItem;
use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\StudyContent\Domain\Repository\ContentItemRepository;

final class InMemoryContentItemRepository implements ContentItemRepository
{
    /** @var array<string, ContentItem> */
    private array $items = [];

    public function save(ContentItem $contentItem): void
    {
        $this->items[$contentItem->id()->toString()] = $contentItem;
    }

    public function get(ContentItemId $id): ContentItem
    {
        $item = $this->find($id);

        if ($item === null) {
            throw new \RuntimeException(sprintf('Content item "%s" not found.', $id->toString()));
        }

        return $item;
    }

    public function find(ContentItemId $id): ?ContentItem
    {
        return $this->items[$id->toString()] ?? null;
    }

    public function getByIds(array $ids): array
    {
        return array_values(array_filter(
            array_map(fn (ContentItemId $id) => $this->find($id), $ids)
        ));
    }
}
