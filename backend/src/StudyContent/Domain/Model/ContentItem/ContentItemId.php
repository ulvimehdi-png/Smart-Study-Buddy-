<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

use InvalidArgumentException;

final readonly class ContentItemId
{
    public function __construct(private string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('ContentItemId cannot be empty.',);
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
