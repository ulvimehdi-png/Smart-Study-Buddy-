<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

use InvalidArgumentException;

final readonly class SourceReference
{
    public function __construct(
        private string $type,
        private string $value,
    )
    {
        if (trim($type) === '' || trim($value) === '') {
            throw new InvalidArgumentException('SourceReference fields cannot be empty.',);
        }
    }

    public static function uploadedFile(string $storageKey): self
    {
        return new self('uploaded_file', $storageKey,);
    }

    public static function externalUrl(string $url): self
    {
        return new self('external_url', $url,);
    }

    public static function manualAuthorship(string $description): self
    {
        return new self('manual_authorship', $description,);
    }

    public function type(): string
    {
        return $this->type;
    }

    public function value(): string
    {
        return $this->value;
    }
}
