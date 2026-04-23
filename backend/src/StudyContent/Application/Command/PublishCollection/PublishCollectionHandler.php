<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Command\PublishCollection;

use App\StudyContent\Application\Port\Clock;
use App\StudyContent\Application\Port\EventBus;
use App\StudyContent\Application\Port\TransactionRunner;
use App\StudyContent\Domain\Model\Collection\CollectionId;
use App\StudyContent\Domain\Repository\CollectionRepository;
use App\StudyContent\Domain\Service\CollectionMembershipPolicy;

final readonly class PublishCollectionHandler
{
    public function __construct(
        private CollectionRepository       $collections,
        private CollectionMembershipPolicy $membershipPolicy,
        private Clock                      $clock,
        private TransactionRunner          $transactions,
        private EventBus                   $eventBus,
    )
    {
    }

    public function __invoke(PublishCollection $command): void
    {
        $this->transactions->run(function () use (
            $command
        ): void {
            $collection = $this->collections->get(CollectionId::fromString($command->collectionId));

            $this->membershipPolicy->assertCollectionCanBePublished($collection);

            $collection->publish(publishedBy: $command->publishedBy,
                now: $this->clock->now());

            $this->collections->save($collection);
            $this->eventBus->publishAll($collection->releaseEvents());
        });
    }
}
