<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Event;

use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\StudyContent\Domain\Model\ContentItem\RelationshipType;
use App\StudyContent\Domain\Shared\DomainEvent;
use DateTimeImmutable;

final readonly class RelationshipDeclared
    implements DomainEvent
{
    public function __construct(
        private ContentItemId     $contentItemId,
        private RelationshipType  $type,
        private ContentItemId     $targetId,
        private DateTimeImmutable $occurredAt,
    )
    {
    }

    public function contentItemId(): ContentItemId
    {
        return $this->contentItemId;
    }

    public function type(): RelationshipType
    {
        return $this->type;
    }

    public function targetId(): ContentItemId
    {
        return $this->targetId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
