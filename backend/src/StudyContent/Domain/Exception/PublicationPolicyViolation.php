<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Exception;

final class PublicationPolicyViolation
    extends DomainException
{
    public static function personalScopeCannotUseSharedChannel(): self
    {
        return new self('Personal-scope content cannot be published to a shared channel.',);
    }

    public static function onlyInstitutionManagedMayUseInstitutionChannel(): self
    {
        return new self('Only institution-managed content may be published to the institution library.',);
    }

    public static function canonicalDuplicateCannotBeShared(): self
    {
        return new self('A duplicate-of-canonical item cannot be published to shared or institution channels.',);
    }

    public static function supersededContentCannotBePublished(): self
    {
        return new self('Superseded content cannot be newly published.',);
    }

    public static function blockedSourcePreventsPublication(string $sourceId): self
    {
        return new self(sprintf('Publication is not allowed because source content "%s" is blocked.',
            $sourceId,),);
    }
}
