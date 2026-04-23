<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Command\ImportSourceMaterial;

use App\StudyContent\Application\Port\BinaryAssetGateway;
use App\StudyContent\Application\Port\Clock;
use App\StudyContent\Application\Port\ContentItemIdentityGenerator;
use App\StudyContent\Application\Port\EventBus;
use App\StudyContent\Application\Port\TransactionRunner;
use App\StudyContent\Application\Result\CommandResult;
use App\StudyContent\Domain\Model\ContentItem\ContentItem;
use App\StudyContent\Domain\Model\ContentItem\ContentTitle;
use App\StudyContent\Domain\Model\ContentItem\OwnerId;
use App\StudyContent\Domain\Repository\ContentItemRepository;

final readonly class ImportSourceMaterialHandler
{
    public function __construct(
        private ContentItemRepository        $contentItems,
        private ContentItemIdentityGenerator $identityGenerator,
        private BinaryAssetGateway           $binaryAssets,
        private Clock                        $clock,
        private TransactionRunner            $transactions,
        private EventBus                     $eventBus
    )
    {
    }

    public function __invoke(ImportSourceMaterial $command): CommandResult
    {
        return $this->transactions->run(function () use ($command): CommandResult {
            $id = $this->identityGenerator->next();

            $sourceReference = $this->binaryAssets->registerUploadedFile(
                originalFilename: $command->originalFilename,
                mimeType: $command->mimeType,
                temporaryPath: $command->temporaryPath,);

            $contentItem = ContentItem::importSourceMaterial(id: $id,
                ownerId: new OwnerId($command->ownerId,),
                title: new ContentTitle($command->title,),
                scope: $command->scope,
                origin: $sourceReference,
                body: null,
                publicationPolicy: null,
                now: $this->clock->now(),);

            $this->contentItems->save($contentItem);
            $this->eventBus->publishAll($contentItem->releaseEvents());

            return new CommandResult($id->toString(),);
        },);
    }
}
