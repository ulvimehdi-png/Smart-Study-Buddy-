<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

use DateTimeImmutable;

final readonly class ProvenanceSource
{
    public function __construct(
        private ContentItemId|SourceReference $source,
        private DerivationMethod              $method,
        private DateTimeImmutable             $recordedAt,
    )
    {
    }

    public static function importedFrom(
        SourceReference   $reference,
        DateTimeImmutable $recordedAt,
    ): self
    {
        return new self($reference, DerivationMethod::MANUAL, $recordedAt,);
    }

    public static function derivedFrom(
        ContentItemId     $sourceId,
        DerivationMethod  $method,
        DateTimeImmutable $recordedAt,
    ): self
    {
        return new self($sourceId, $method, $recordedAt,);
    }

    public function source(): ContentItemId|SourceReference
    {
        return $this->source;
    }

    public function method(): DerivationMethod
    {
        return $this->method;
    }

    public function recordedAt(): DateTimeImmutable
    {
        return $this->recordedAt;
    }
}
