<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\Collection;

use InvalidArgumentException;

final class CollectionTitle
{
    public function __construct(private string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException('Collection title cannot be empty.',);
        }

        if (mb_strlen($value) > 200) {
            throw new InvalidArgumentException('Collection title is too long.',);
        }

        $this->value = $value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
