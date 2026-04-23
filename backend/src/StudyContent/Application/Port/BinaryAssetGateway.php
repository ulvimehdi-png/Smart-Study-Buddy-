<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Port;

use App\StudyContent\Domain\Model\ContentItem\SourceReference;

interface BinaryAssetGateway
{
    public function registerUploadedFile(
        string $originalFilename,
        string $mimeType,
        string $temporaryPath,
    ): SourceReference;
}
