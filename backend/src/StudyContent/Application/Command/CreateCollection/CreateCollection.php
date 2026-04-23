<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Command\CreateCollection;

use App\StudyContent\Domain\Model\ContentItem\SharingScope;

final readonly class CreateCollection
{
    public function __construct(
        public string       $ownerId,
        public string       $title,
        public SharingScope $scope,
    )
    {
    }
}
