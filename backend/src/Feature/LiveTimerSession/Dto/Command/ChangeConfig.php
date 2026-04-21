<?php

namespace App\Feature\LiveTimerSession\Dto\Command;

use App\Feature\LiveTimerSession\Dto\DurationDto;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangeConfig
{
    public function __construct(
        #[Assert\Valid]
        public ?DurationDto $work = null,

        #[Assert\Valid]
        public ?DurationDto $break = null,
    )
    {
    }
}
