<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

use InvalidArgumentException;

final readonly class ContentBody
{
    public function __construct(private string $value)
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException('Content body cannot be blank.',);
        }
    }

    public function toString(): string
    {
        return $this->value;
    }
}
