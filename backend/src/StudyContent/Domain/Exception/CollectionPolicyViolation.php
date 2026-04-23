<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Exception;

final class CollectionPolicyViolation
    extends DomainException
{
    public static function blockedContentCannotBeAdded(string $contentItemId): self
    {
        return new self(sprintf('Blocked content "%s" cannot be added to a collection.',
            $contentItemId,),);
    }

    public static function personalContentCannotEnterSharedCollection(
        string $contentItemId,
    ): self
    {
        return new self(sprintf('Personal content "%s" cannot be added to a shared-group collection.',
            $contentItemId,),);
    }

    public static function onlyInstitutionManagedContentMayEnterInstitutionCollection(
        string $contentItemId,
    ): self
    {
        return new self(sprintf('Only institution-managed content may be added to an institution-managed collection. Offending item: "%s".',
            $contentItemId,),);
    }

    public static function canonicalDuplicateCannotEnterInstitutionCollection(
        string $contentItemId,
    ): self
    {
        return new self(sprintf('Duplicate-of-canonical content "%s" cannot be added to an institution-managed collection.',
            $contentItemId,),);
    }

    public static function deprecatedContentCannotEnterPublishedCollection(
        string $contentItemId,
    ): self
    {
        return new self(sprintf('Deprecated content "%s" cannot be added to a published collection.',
            $contentItemId,),);
    }

    public static function archivedContentCannotEnterPublishedCollection(
        string $contentItemId,
    ): self
    {
        return new self(sprintf('Archived content "%s" cannot be present in a published collection.',
            $contentItemId,),);
    }

    public static function missingMember(string $contentItemId): self
    {
        return new self(sprintf('Collection member "%s" could not be resolved.',
            $contentItemId,),);
    }
}
