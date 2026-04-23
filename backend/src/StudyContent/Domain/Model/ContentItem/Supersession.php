<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

use DateTimeImmutable;

final readonly class Supersession
{
    public function __construct(
        private ContentItemId     $successorId,
        private DateTimeImmutable $supersededAt,
    )
    {
    }

    public function successorId(): ContentItemId
    {
        return $this->successorId;
    }

    public function supersededAt(): DateTimeImmutable
    {
        return $this->supersededAt;
    }
}
