<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Port;

use App\StudyContent\Domain\Shared\DomainEvent;

interface EventBus
{
    /**
     * @param list<DomainEvent> $events
     */
    public function publishAll(array $events): void;
}
