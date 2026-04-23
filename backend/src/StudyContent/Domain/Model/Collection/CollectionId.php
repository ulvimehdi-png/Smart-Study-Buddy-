<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\Collection;

use InvalidArgumentException;

final readonly class CollectionId
{
    public function __construct(private string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('CollectionId cannot be empty.',);
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value,);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
