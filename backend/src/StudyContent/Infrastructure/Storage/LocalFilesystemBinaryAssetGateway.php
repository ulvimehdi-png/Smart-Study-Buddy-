<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Storage;

use App\StudyContent\Application\Port\BinaryAssetGateway;
use App\StudyContent\Domain\Model\ContentItem\SourceReference;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Uid\Uuid;

final readonly class LocalFilesystemBinaryAssetGateway implements BinaryAssetGateway
{
    public function __construct(
        private string     $baseDirectory,
        private Filesystem $filesystem
    )
    {
    }

    public function registerUploadedFile(
        string $originalFilename,
        string $mimeType,
        string $temporaryPath
    ): SourceReference
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $filename = Uuid::v7()->toRfc4122() . ($extension !== '' ? '.' . $extension : '');
        $targetPath = rtrim($this->baseDirectory, '/') . '/' . $filename;

        $this->filesystem->copy($temporaryPath, $targetPath, true);

        return SourceReference::uploadedFile($filename);
    }
}
