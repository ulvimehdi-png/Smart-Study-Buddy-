<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\Collection;

use InvalidArgumentException;

final readonly class CollectionEntryPosition
{
    public function __construct(private int $value)
    {
        if ($value < 1) {
            throw new InvalidArgumentException('Collection entry position must be >= 1.',);
        }
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
