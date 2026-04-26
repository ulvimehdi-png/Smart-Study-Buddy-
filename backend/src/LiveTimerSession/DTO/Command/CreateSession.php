<?php

namespace App\LiveTimerSession\DTO\Command;

use App\LiveTimerSession\DTO\DurationDto;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateSession
{
    public function __construct(
        #[Assert\Valid]
        public readonly DurationDto $work = new DurationDto(25, 0),

        #[Assert\Valid]
        public readonly DurationDto $break = new DurationDto(5, 0),

        public readonly ?int $subjectId = null,
        public readonly ?int $taskId = null
    ) {
    }
}
