<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Command\ImportSourceMaterial;

use App\StudyContent\Domain\Model\ContentItem\SharingScope;

final readonly class ImportSourceMaterial
{
    public function __construct(
        public string       $ownerId,
        public string       $title,
        public SharingScope $scope,
        public string       $originalFilename,
        public string       $mimeType,
        public string       $temporaryPath,
    )
    {
    }
}
