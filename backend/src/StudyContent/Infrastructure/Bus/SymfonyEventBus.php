<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Bus;

use App\StudyContent\Application\Port\EventBus;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class SymfonyEventBus
    implements EventBus
{
    public function __construct(
        private MessageBusInterface $eventBus
    )
    {
    }

    public function publishAll(array $events): void
    {
        foreach ($events as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
