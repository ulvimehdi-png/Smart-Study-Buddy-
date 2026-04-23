<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Exception;

final class ReviewRequired
    extends DomainException
{
    public static function beforePublication(): self
    {
        return new self('Content requires approval before publication.',);
    }
}
