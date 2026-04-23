<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

use InvalidArgumentException;

final readonly class OwnerId
{
    public function __construct(private string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('OwnerId cannot be empty.',);
        }
    }

    public function toString(): string
    {
        return $this->value;
    }
}
