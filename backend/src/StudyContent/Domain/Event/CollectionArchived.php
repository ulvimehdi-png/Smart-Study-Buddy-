<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Event;

use App\StudyContent\Domain\Model\Collection\CollectionId;
use App\StudyContent\Domain\Shared\DomainEvent;
use DateTimeImmutable;

final readonly class CollectionArchived
    implements DomainEvent
{
    public function __construct(
        private CollectionId      $collectionId,
        private DateTimeImmutable $occurredAt,
    )
    {
    }

    public function collectionId(): CollectionId
    {
        return $this->collectionId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
