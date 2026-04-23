<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Query\GetCollectionView;

final readonly class GetCollectionViewHandler
{
    public function __construct(
        public string $contentItemId,
    )
    {
    }
}
