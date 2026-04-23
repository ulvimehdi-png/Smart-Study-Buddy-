<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Event;

use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\StudyContent\Domain\Model\ContentItem\PublicationChannel;
use App\StudyContent\Domain\Shared\DomainEvent;
use DateTimeImmutable;

final readonly class ContentPublished
    implements DomainEvent
{
    public function __construct(
        private ContentItemId      $contentItemId,
        private PublicationChannel $channel,
        private DateTimeImmutable  $occurredAt,
    )
    {
    }

    public function contentItemId(): ContentItemId
    {
        return $this->contentItemId;
    }

    public function channel(): PublicationChannel
    {
        return $this->channel;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
