<?php

declare(strict_types=1);

namespace App\Tests\StudyContent\Support\Double;

use App\StudyContent\Application\Port\Clock;

final readonly class FixedClock implements Clock
{
    public function __construct(
        private \DateTimeImmutable $now
    ) {
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }
}
