<?php

namespace App\Dto\LiveTimer;

use Symfony\Component\Validator\Constraints as Assert;

final class DurationDto
{
    public function __construct(
        #[Assert\GreaterThanOrEqual(0)]
        public readonly int $minutes = 0,

        #[Assert\Range(min: 0, max: 59)]
        public readonly int $seconds = 0,
    ) {
    }
}
