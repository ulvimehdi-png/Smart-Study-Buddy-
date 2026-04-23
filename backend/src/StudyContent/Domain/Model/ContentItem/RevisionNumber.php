<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

use InvalidArgumentException;

final readonly class RevisionNumber
{
    public function __construct(private int $value)
    {
        if ($value < 1) {
            throw new InvalidArgumentException('Revision number must be >= 1.',);
        }
    }

    public static function first(): self
    {
        return new self(1,);
    }

    public function next(): self
    {
        return new self($this->value + 1,);
    }

    public function toInt(): int
    {
        return $this->value;
    }
}
