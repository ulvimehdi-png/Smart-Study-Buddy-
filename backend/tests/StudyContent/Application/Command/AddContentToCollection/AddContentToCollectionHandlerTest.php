<?php

declare(strict_types=1);

namespace App\Tests\StudyContent\Application\Command\AddContentToCollection;

use App\StudyContent\Application\Command\AddContentToCollection\AddContentToCollection;
use App\StudyContent\Application\Command\AddContentToCollection\AddContentToCollectionHandler;
use App\StudyContent\Domain\Model\Collection\CollectionId;
use App\StudyContent\Domain\Service\DefaultCollectionMembershipPolicy;
use App\StudyContent\Domain\Service\DefaultPublicationPolicyService;
use App\Tests\StudyContent\Support\Builder\CollectionBuilder;
use App\Tests\StudyContent\Support\Builder\ContentItemBuilder;
use App\Tests\StudyContent\Support\Double\CollectingEventBus;
use App\Tests\StudyContent\Support\Double\FixedClock;
use App\Tests\StudyContent\Support\Double\ImmediateTransactionRunner;
use App\Tests\StudyContent\Support\Double\InMemoryCollectionRepository;
use App\Tests\StudyContent\Support\Double\InMemoryContentItemRepository;
use PHPUnit\Framework\TestCase;

final class AddContentToCollectionHandlerTest extends TestCase
{
    public function test_add_content_to_collection_creates_entry(): void
    {
        $collections = new InMemoryCollectionRepository();
        $contentItems = new InMemoryContentItemRepository();
        $eventBus = new CollectingEventBus();

        $publicationPolicy = new DefaultPublicationPolicyService($contentItems);
        $membershipPolicy = new DefaultCollectionMembershipPolicy($contentItems, $publicationPolicy);

        $collection = (new CollectionBuilder())
            ->withId('collection-1')
            ->build();
        $collections->save($collection);

        $content = (new ContentItemBuilder())
            ->withId('content-1')
            ->buildDraft();
        $contentItems->save($content);

        $handler = new AddContentToCollectionHandler(
            collections: $collections,
            contentItems: $contentItems,
            membershipPolicy: $membershipPolicy,
            clock: new FixedClock(new \DateTimeImmutable('2026-04-22T12:00:00+00:00')),
            transactions: new ImmediateTransactionRunner(),
            eventBus: $eventBus
        );

        $handler(new AddContentToCollection(
            collectionId: 'collection-1',
            contentItemId: 'content-1',
            curationNote: 'Core summary'
        ));

        $saved = $collections->get(CollectionId::fromString('collection-1'));

        self::assertCount(1, $saved->entries());
        self::assertSame('content-1', $saved->entries()[0]->contentItemId()->toString());
        self::assertCount(1, $eventBus->released());
    }
}
