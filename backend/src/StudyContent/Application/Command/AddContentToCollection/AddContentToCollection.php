<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Command\AddContentToCollection;

final readonly class AddContentToCollection
{
    public function __construct(
        public string  $collectionId,
        public string  $contentItemId,
        public ?string $curationNote = null,
    )
    {
    }
}
