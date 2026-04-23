<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

use DateTimeImmutable;

final readonly class ReviewRecord
{
    public function __construct(
        private string               $reviewerId,
        private ReviewOutcome        $outcome,
        private PolicyDecisionReason $reason,
        private DateTimeImmutable    $reviewedAt,
    )
    {
    }

    public function reviewerId(): string
    {
        return $this->reviewerId;
    }

    public function outcome(): ReviewOutcome
    {
        return $this->outcome;
    }

    public function reason(): PolicyDecisionReason
    {
        return $this->reason;
    }

    public function reviewedAt(): DateTimeImmutable
    {
        return $this->reviewedAt;
    }
}
