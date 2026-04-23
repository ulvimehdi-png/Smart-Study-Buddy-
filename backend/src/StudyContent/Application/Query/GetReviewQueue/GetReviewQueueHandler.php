<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Query\GetReviewQueue;

final readonly class GetReviewQueueHandler
{
    public function __construct(
        public string $contentItemId,
    )
    {
    }
}
