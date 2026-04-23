<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Command\ApproveContent;

final readonly class ApproveContent
{
    public function __construct(
        public string $contentItemId,
        public string $reviewerId,
        public string $reason,
    )
    {
    }
}
