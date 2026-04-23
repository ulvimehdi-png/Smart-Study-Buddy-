<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

use InvalidArgumentException;

final class PolicyDecisionReason
{
    public function __construct(private string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException('Policy decision reason cannot be empty.',);
        }

        if (mb_strlen($value) > 100) {
            throw new InvalidArgumentException('Policy decision reason is too long.',);
        }

        $this->value = $value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
