<?php

namespace App\Feature\LiveTimerSession\Dto\Command;

use App\Feature\LiveTimerSession\Dto\DurationDto;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateSession
{
    public function __construct(
        #[Assert\Valid]
        public DurationDto $work = new DurationDto(25, 0),

        #[Assert\Valid]
        public DurationDto $break = new DurationDto(5, 0),

        public ?int        $subjectId = null,
        public ?int        $taskId = null
    )
    {
    }
}
