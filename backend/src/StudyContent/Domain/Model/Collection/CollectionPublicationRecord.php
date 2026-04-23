<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\Collection;

use App\StudyContent\Domain\Model\ContentItem\SharingScope;
use DateTimeImmutable;

final readonly class CollectionPublicationRecord
{
    public function __construct(
        private SharingScope      $scope,
        private string            $publishedBy,
        private DateTimeImmutable $publishedAt,
    )
    {
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
