<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

use DateTimeImmutable;

final readonly class Relationship
{
    public function __construct(
        private RelationshipType  $type,
        private ContentItemId     $targetId,
        private DateTimeImmutable $recordedAt,
    )
    {
    }

    public function type(): RelationshipType
    {
        return $this->type;
    }

    public function targetId(): ContentItemId
    {
        return $this->targetId;
    }

    public function recordedAt(): DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function sameAs(self $other): bool
    {
        return $this->type === $other->type && $this->targetId->equals($other->targetId);
    }
}
