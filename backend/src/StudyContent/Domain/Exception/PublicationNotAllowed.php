<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Exception;

final class PublicationNotAllowed
    extends DomainException
{
    public static function blocked(): self
    {
        return new self('Blocked content cannot be published.',);
    }

    public static function archived(): self
    {
        return new self('Archived content cannot be published.',);
    }

    public static function deprecated(): self
    {
        return new self('Deprecated content cannot be actively published.',);
    }

    public static function unsupportedScope(string $scope): self
    {
        return new self(sprintf('Publication is not allowed for sharing scope "%s".',
            $scope,),);
    }
}
