<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Exception;

final class CollectionMembershipNotAllowed
    extends DomainException
{
    public static function duplicateContent(string $contentItemId): self
    {
        return new self(sprintf('Content item "%s" is already a member of the collection.',
            $contentItemId,),);
    }

    public static function archivedCollection(): self
    {
        return new self('Archived collection cannot be modified.',);
    }

    public static function emptyPublishedCollection(): self
    {
        return new self('An empty collection cannot be published.',);
    }
}
