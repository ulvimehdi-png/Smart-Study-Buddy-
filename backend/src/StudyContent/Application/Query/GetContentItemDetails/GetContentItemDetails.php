<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Query\GetContentItemDetails;

final readonly class GetContentItemDetails
{
    public function __construct(
        public string $contentItemId,
    )
    {
    }
}
