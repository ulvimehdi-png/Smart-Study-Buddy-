<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Command\PublishContent;

use App\StudyContent\Application\Port\Clock;
use App\StudyContent\Application\Port\EventBus;
use App\StudyContent\Application\Port\TransactionRunner;
use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\StudyContent\Domain\Repository\ContentItemRepository;
use App\StudyContent\Domain\Service\PublicationPolicyService;

final readonly class PublishContentHandler
{
    public function __construct(
        private ContentItemRepository    $contentItems,
        private PublicationPolicyService $publicationPolicy,
        private Clock                    $clock,
        private TransactionRunner        $transactions,
        private EventBus                 $eventBus,
    )
    {
    }

    public function __invoke(PublishContent $command): void
    {
        $this->transactions->run(function () use (
            $command,
        ): void {
            $contentItem = $this->contentItems->get(ContentItemId::fromString($command->contentItemId));

            $this->publicationPolicy->assertContentCanBePublished($contentItem,
                $command->channel,);

            $contentItem->publish(channel: $command->channel,
                publishedBy: $command->publishedBy,
                now: $this->clock->now(),);

            $this->contentItems->save($contentItem);
            $this->eventBus->publishAll($contentItem->releaseEvents());
        },);
    }
}
