<?php

declare(strict_types=1);

namespace App\Tests\StudyContent\Application\Command;

use App\StudyContent\Application\Command\PublishCollection\PublishCollection;
use App\StudyContent\Application\Command\PublishCollection\PublishCollectionHandler;
use App\StudyContent\Domain\Model\ContentItem\PolicyDecisionReason;
use App\StudyContent\Domain\Model\ContentItem\SharingScope;
use App\StudyContent\Domain\Service\DefaultCollectionMembershipPolicy;
use App\StudyContent\Domain\Service\DefaultPublicationPolicyService;
use App\Tests\StudyContent\Support\Builder\CollectionBuilder;
use App\Tests\StudyContent\Support\Builder\ContentItemBuilder;
use App\Tests\StudyContent\Support\Double\CollectingEventBus;
use App\Tests\StudyContent\Support\Double\FixedClock;
use App\Tests\StudyContent\Support\Double\InMemoryCollectionRepository;
use App\Tests\StudyContent\Support\Double\InMemoryContentItemRepository;
use PHPUnit\Framework\TestCase;

final class PublishCollectionHandlerTest extends TestCase
{
    public function test_publish_collection_marks_collection_as_published(): void
    {
        $collections = new InMemoryCollectionRepository();
        $contentItems = new InMemoryContentItemRepository();

        $publicationPolicy = new DefaultPublicationPolicyService($contentItems);
        $membershipPolicy = new DefaultCollectionMembershipPolicy($contentItems, $publicationPolicy);

        $content = (new ContentItemBuilder())
            ->withId('content-1')
            ->withScope(SharingScope::SHARED_GROUP)
            ->buildDraft();
        $content->submitForReview();
        $content->approve('reviewer-1', new PolicyDecisionReason('Approved.'));
        $contentItems->save($content);

        $collection = (new CollectionBuilder())
            ->withId('collection-1')
            ->withScope(SharingScope::SHARED_GROUP)
            ->build();
        $collection->addEntry($content->id());
        $collections->save($collection);

        $handler = new PublishCollectionHandler(
            collections: $collections,
            membershipPolicy: $membershipPolicy,
            clock: new FixedClock(new \DateTimeImmutable('2026-04-22T12:00:00+00:00')),
            transactions: new class implements \App\StudyContent\Application\Port\TransactionRunner {
                public function run(callable $callback): mixed { return $callback(); }
            },
            eventBus: new CollectingEventBus()
        );

        $handler(new PublishCollection(
            collectionId: 'collection-1',
            publishedBy: 'owner-1'
        ));

        self::assertSame('published', $collections->get(
            \App\StudyContent\Domain\Model\Collection\CollectionId::fromString('collection-1')
        )->status()->value);
    }
}
