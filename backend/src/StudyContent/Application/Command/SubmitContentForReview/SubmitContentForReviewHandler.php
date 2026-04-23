<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Command\SubmitContentForReview;

use App\StudyContent\Application\Port\Clock;
use App\StudyContent\Application\Port\EventBus;
use App\StudyContent\Application\Port\TransactionRunner;
use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\StudyContent\Domain\Repository\ContentItemRepository;

final readonly class SubmitContentForReviewHandler
{
    public function __construct(
        private ContentItemRepository $contentItems,
        private Clock                 $clock,
        private TransactionRunner     $transactions,
        private EventBus              $eventBus,
    )
    {
    }

    public function __invoke(SubmitContentForReview $command): void
    {
        $this->transactions->run(function () use (
            $command,
        ): void {
            $contentItem = $this->contentItems->get(ContentItemId::fromString($command->contentItemId));

            $contentItem->submitForReview($this->clock->now());

            $this->contentItems->save($contentItem);
            $this->eventBus->publishAll($contentItem->releaseEvents());
        },);
    }
}
