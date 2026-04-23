<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Exception;

final class SupersessionConflict
    extends DomainException
{
    public static function alreadySuperseded(): self
    {
        return new self('Content item is already superseded.',);
    }

    public static function selfSupersession(): self
    {
        return new self('A content item cannot supersede itself.',);
    }
}
