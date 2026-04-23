<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Exception;

final class MissingProvenance
    extends DomainException
{
    public static function derivativeMustHaveProvenance(): self
    {
        return new self('Derivative content must have at least one Provenance source.',);
    }

    public static function importedContentMustHaveProvenance(): self
    {
        return new self('Imported content must declare its Provenance.',);
    }
}
