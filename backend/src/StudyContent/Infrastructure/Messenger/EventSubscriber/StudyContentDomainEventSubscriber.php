<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Messenger\EventSubscriber;

use App\StudyContent\Domain\Event\ContentImported;
use App\StudyContent\Infrastructure\Messenger\Message\ExtractImportedContent;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class StudyContentDomainEventSubscriber
{
    public function __construct(
        private MessageBusInterface $asyncBus
    )
    {
    }

    public function __invoke(ContentImported $event): void
    {
        $this->asyncBus->dispatch(new ExtractImportedContent($event
            ->contentItemId()
            ->toString()));
    }
}
