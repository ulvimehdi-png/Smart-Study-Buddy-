<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Command\CreateCollection;

use App\StudyContent\Application\Port\CollectionIdentityGenerator;
use App\StudyContent\Application\Port\TransactionRunner;
use App\StudyContent\Application\Result\CommandResult;
use App\StudyContent\Domain\Model\Collection\Collection;
use App\StudyContent\Domain\Model\Collection\CollectionTitle;
use App\StudyContent\Domain\Model\ContentItem\OwnerId;
use App\StudyContent\Domain\Repository\CollectionRepository;

final readonly class CreateCollectionHandler
{
    public function __construct(
        private CollectionRepository        $collections,
        private CollectionIdentityGenerator $identityGenerator,
        private TransactionRunner           $transactions
    )
    {
    }

    public function __invoke(CreateCollection $command): CommandResult
    {
        return $this->transactions->run(function () use ($command): CommandResult {
            $id = $this->identityGenerator->next();

            $collection = Collection::create(
                id: $id,
                title: new CollectionTitle($command->title),
                ownerId: new OwnerId($command->ownerId),
                scope: $command->scope
            );

            $this->collections->save($collection);

            return new CommandResult($id->toString());
        });
    }
}
