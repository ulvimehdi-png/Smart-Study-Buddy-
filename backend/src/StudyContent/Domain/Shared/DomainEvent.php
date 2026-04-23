<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Shared;

use DateTimeImmutable;

interface DomainEvent
{
    public function occurredAt(): DateTimeImmutable;
}
