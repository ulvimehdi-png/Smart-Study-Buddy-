<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Command\CreateDerivativeContent;

use App\StudyContent\Application\Port\Clock;
use App\StudyContent\Application\Port\EventBus;
use App\StudyContent\Application\Port\TransactionRunner;
use App\StudyContent\Application\Result\CommandResult;
use App\StudyContent\Domain\Model\ContentItem\ContentBody;
use App\StudyContent\Domain\Model\ContentItem\ContentItem;
use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\StudyContent\Domain\Model\ContentItem\ContentTitle;
use App\StudyContent\Domain\Model\ContentItem\OwnerId;
use App\StudyContent\Domain\Repository\ContentItemRepository;
use App\StudyContent\Domain\Service\DerivationPolicyService;

final readonly class CreateDerivativeContentHandler
{
    public function __construct(
        private ContentItemRepository   $contentItems,
        private DerivationPolicyService $derivationPolicy,
        private Clock                   $clock,
        private TransactionRunner       $transactions,
        private EventBus                $eventBus,
    )
    {
    }

    public function __invoke(CreateDerivativeContent $command): CommandResult
    {
        return $this->transactions->run(function () use (
            $command,
        ): CommandResult {
            $sourceIds = array_map(static fn(
                string $id,
            ): ContentItemId => ContentItemId::fromString($id), $command->sourceIds,);

            $this->derivationPolicy->assertCanDeriveFromSources($sourceIds,
                $command->kind,);

            $id = $this->contentItems->nextIdentity();

            $contentItem = ContentItem::createDerivative(id: $id,
                ownerId: new OwnerId($command->ownerId,),
                kind: $command->kind,
                title: new ContentTitle($command->title,),
                body: new ContentBody($command->body,),
                scope: $command->scope,
                sourceIds: $sourceIds,
                derivationMethod: $command->derivationMethod,
                publicationPolicy: null,
                now: $this->clock->now(),);

            $this->contentItems->save($contentItem);
            $this->eventBus->publishAll($contentItem->releaseEvents());

            return new CommandResult($id->toString(),);
        },);
    }
}
