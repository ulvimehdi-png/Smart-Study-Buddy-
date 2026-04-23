<?php

declare(strict_types=1);

namespace App\Tests\StudyContent\Support\Double;

use App\StudyContent\Application\Port\EventBus;
use App\StudyContent\Domain\Shared\DomainEvent;

final class CollectingEventBus implements EventBus
{
    /** @var list<DomainEvent> */
    private array $events = [];

    public function publishAll(array $events): void
    {
        foreach ($events as $event) {
            $this->events[] = $event;
        }
    }

    /**
     * @return list<DomainEvent>
     */
    public function released(): array
    {
        return $this->events;
    }
}
