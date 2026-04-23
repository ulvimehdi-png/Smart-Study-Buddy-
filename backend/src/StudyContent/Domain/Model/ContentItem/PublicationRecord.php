<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

use DateTimeImmutable;

final readonly class PublicationRecord
{
    public function __construct(
        private PublicationChannel $channel,
        private SharingScope       $scope,
        private string             $publishedBy,
        private DateTimeImmutable  $publishedAt,
    )
    {
    }

    public function channel(): PublicationChannel
    {
        return $this->channel;
    }

    public function scope(): SharingScope
    {
        return $this->scope;
    }

    public function publishedBy(): string
    {
        return $this->publishedBy;
    }

    public function publishedAt(): DateTimeImmutable
    {
        return $this->publishedAt;
    }
}
