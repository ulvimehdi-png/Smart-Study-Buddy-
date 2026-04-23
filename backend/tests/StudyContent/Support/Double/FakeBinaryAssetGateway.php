<?php

declare(strict_types=1);

namespace App\Tests\StudyContent\Support\Double;

use App\StudyContent\Application\Port\BinaryAssetGateway;
use App\StudyContent\Domain\Model\ContentItem\SourceReference;

final class FakeBinaryAssetGateway implements BinaryAssetGateway
{
    public function registerUploadedFile(
        string $originalFilename,
        string $mimeType,
        string $temporaryPath
    ): SourceReference {
        return SourceReference::uploadedFile('fake-storage/' . basename($originalFilename));
    }
}
