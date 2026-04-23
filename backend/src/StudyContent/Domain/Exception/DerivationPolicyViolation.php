<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Exception;

final class DerivationPolicyViolation
    extends DomainException
{
    public static function targetKindCannotBeSourceDocument(): self
    {
        return new self('Derived content cannot have kind "source_document".',);
    }

    public static function blockedSource(string $contentItemId): self
    {
        return new self(sprintf('Cannot derive from blocked source content "%s".',
            $contentItemId,),);
    }

    public static function archivedSource(string $contentItemId): self
    {
        return new self(sprintf('Cannot derive new content from archived source "%s".',
            $contentItemId,),);
    }
}
