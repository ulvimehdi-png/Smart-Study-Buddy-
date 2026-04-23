<?php

declare(strict_types=1);

namespace App\Tests\StudyContent\Domain\Model;

use App\StudyContent\Domain\Exception\CollectionMembershipNotAllowed;
use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\Tests\StudyContent\Support\Builder\CollectionBuilder;
use PHPUnit\Framework\TestCase;

final class CollectionTest extends TestCase
{
    public function test_entry_can_be_added(): void
    {
        $collection = new CollectionBuilder()->build();

        $collection->addEntry(ContentItemId::fromString('content-1'));

        self::assertCount(1, $collection->entries());
        self::assertSame(1, $collection->entries()[0]->position()->toInt());
    }

    public function test_duplicate_membership_is_rejected(): void
    {
        $collection = new CollectionBuilder()->build();
        $collection->addEntry(ContentItemId::fromString('content-1'));

        $this->expectException(CollectionMembershipNotAllowed::class);

        $collection->addEntry(ContentItemId::fromString('content-1'));
    }

    public function test_reordering_preserves_contiguous_positions(): void
    {
        $collection = new CollectionBuilder()->build();
        $collection->addEntry(ContentItemId::fromString('content-1'));
        $collection->addEntry(ContentItemId::fromString('content-2'));
        $collection->addEntry(ContentItemId::fromString('content-3'));

        $collection->reorder(
            ContentItemId::fromString('content-3'),
            new \App\StudyContent\Domain\Model\Collection\CollectionEntryPosition(1)
        );

        self::assertSame('content-3', $collection->entries()[0]->contentItemId()->toString());
        self::assertSame(1, $collection->entries()[0]->position()->toInt());
        self::assertSame(2, $collection->entries()[1]->position()->toInt());
        self::assertSame(3, $collection->entries()[2]->position()->toInt());
    }
}
