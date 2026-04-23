<?php

namespace App\StudyContent\Domain\Model\ContentItem;

use InvalidArgumentException;

final class ContentTitle
{
    public function __construct(private string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException('Content title cannot be empty.',);
        }

        if (mb_strlen($value) > 50) {
            throw new InvalidArgumentException('Content title is too long.',);
        }

        $this->value = $value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
