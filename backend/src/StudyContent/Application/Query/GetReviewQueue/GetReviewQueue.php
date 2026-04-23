<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Query\GetReviewQueue;

final readonly class GetReviewQueue
{
    public function __construct(
        public string $contentItemId,
    )
    {
    }
}
