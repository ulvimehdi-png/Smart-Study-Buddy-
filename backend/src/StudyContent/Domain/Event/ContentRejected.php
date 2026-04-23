<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Event;

use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\StudyContent\Domain\Shared\DomainEvent;
use DateTimeImmutable;

final readonly class ContentRejected
    implements DomainEvent
{
    public function __construct(
        private ContentItemId     $contentItemId,
        private string            $reviewerId,
        private DateTimeImmutable $occurredAt,
    )
    {
    }

    public function contentItemId(): ContentItemId
    {
        return $this->contentItemId;
    }

    public function reviewerId(): string
    {
        return $this->reviewerId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
