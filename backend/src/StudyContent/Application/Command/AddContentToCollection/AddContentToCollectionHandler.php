<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Command\AddContentToCollection;

use App\StudyContent\Application\Port\Clock;
use App\StudyContent\Application\Port\EventBus;
use App\StudyContent\Application\Port\TransactionRunner;
use App\StudyContent\Domain\Model\Collection\CollectionId;
use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\StudyContent\Domain\Repository\CollectionRepository;
use App\StudyContent\Domain\Repository\ContentItemRepository;
use App\StudyContent\Domain\Service\CollectionMembershipPolicy;

final readonly class AddContentToCollectionHandler
{
    public function __construct(
        private CollectionRepository       $collections,
        private ContentItemRepository      $contentItems,
        private CollectionMembershipPolicy $membershipPolicy,
        private Clock                      $clock,
        private TransactionRunner          $transactions,
        private EventBus                   $eventBus,
    )
    {
    }

    public function __invoke(AddContentToCollection $command): void
    {
        $this->transactions->run(function () use (
            $command,
        ): void {
            $collection = $this->collections->get(CollectionId::fromString($command->collectionId));

            $contentItem = $this->contentItems->get(ContentItemId::fromString($command->contentItemId));

            $this->membershipPolicy->assertCanBeAdded($collection, $contentItem);

            $collection->addEntry(contentItemId: $contentItem->id(),
                curationNote: $command->curationNote,
                now: $this->clock->now(),);

            $this->collections->save($collection);
            $this->eventBus->publishAll($collection->releaseEvents());
        },);
    }
}
