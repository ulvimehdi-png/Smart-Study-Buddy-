<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Messenger\Message;

final readonly class ExtractImportedContent
{
    public function __construct(
        public string $contentItemId
    )
    {
    }
}
