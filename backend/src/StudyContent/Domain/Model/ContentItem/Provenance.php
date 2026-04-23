<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

use App\StudyContent\Domain\Exception\MissingProvenance;
use DateTimeImmutable;

final class Provenance
{
    /** @var list<ProvenanceSource> */
    private array $sources;

    /**
     * @param list<ProvenanceSource> $sources
     */
    private function __construct(array $sources)
    {
        $this->sources = $sources;
    }

    public static function imported(ProvenanceSource $provenance): self
    {
        return new self([$provenance],);
    }

    /**
     * @param list<ProvenanceSource> $sources
     */
    public static function derived(array $sources): self
    {
        if ($sources === []) {
            throw MissingProvenance::derivativeMustHaveProvenance();
        }

        return new self($sources,);
    }

    public static function manuallyAuthored(DateTimeImmutable $recordedAt): self
    {
        return new self([ProvenanceSource::importedFrom(SourceReference::manualAuthorship('manual authoring'),
            $recordedAt,),],);
    }

    /**
     * @return list<ProvenanceSource>
     */
    public function sources(): array
    {
        return $this->sources;
    }

    public function hasAtLeastOneSource(): bool
    {
        return $this->sources !== [];
    }
}
