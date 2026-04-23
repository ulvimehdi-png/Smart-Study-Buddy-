<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Query\GetContentItemDetails;

use Doctrine\DBAL\Connection;

final readonly class GetContentItemDetailsHandler
{
    public function __construct(
        private Connection $connection,
    )
    {
    }

    public function __invoke(GetContentItemDetails $query): ?ContentItemDetailsView
    {
        // Intentionally DB/read-model oriented.
        // No aggregate loading for read use cases.
        return null;
    }
}
