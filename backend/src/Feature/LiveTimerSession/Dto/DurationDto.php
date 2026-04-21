<?php

namespace App\Feature\LiveTimerSession\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class DurationDto
{
    public function __construct(
        #[Assert\GreaterThanOrEqual(0)]
        public int $minutes = 0,

        #[Assert\Range(min: 0, max: 59)]
        public int $seconds = 0,
    )
    {
    }
}
