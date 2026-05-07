<?php

namespace App\LiveTimerSession\DTO\Command;

use App\LiveTimerSession\DTO\DurationDto;
use Symfony\Component\Validator\Constraints as Assert;

final class ChangeConfig
{
    public function __construct(
        #[Assert\Valid]
        public readonly ?DurationDto $work = null,

        #[Assert\Valid]
        public readonly ?DurationDto $break = null,
    ) {
    }
}
