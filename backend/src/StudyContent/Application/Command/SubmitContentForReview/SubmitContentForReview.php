<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Command\SubmitContentForReview;

final readonly class SubmitContentForReview
{
    public function __construct(
        public string $contentItemId,
    )
    {
    }
}
