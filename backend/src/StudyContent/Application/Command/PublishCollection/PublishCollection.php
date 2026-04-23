<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Command\PublishCollection;

final readonly class PublishCollection
{
    public function __construct(
        public string $collectionId,
        public string $publishedBy,
    )
    {
    }
}
