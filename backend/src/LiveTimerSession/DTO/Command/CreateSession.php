<?php

namespace App\LiveTimerSession\DTO\Command;

use App\LiveTimerSession\DTO\DurationDto;
use App\LiveTimerSession\Enum\SessionPhase;
use App\LiveTimerSession\Enum\SessionStatus;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateSession
{
    public function __construct(
        #[Assert\Valid]
        public readonly DurationDto $work = new DurationDto(25, 0),

        #[Assert\Valid]
        public readonly DurationDto $break = new DurationDto(5, 0),

        #[Assert\Choice(callback: [SessionPhase::class, 'values'])]
        public readonly string $phase = 'focus',

        #[Assert\Choice(callback: [SessionStatus::class, 'shareableValues'])]
        public readonly string $status = 'idle',

        #[Assert\PositiveOrZero]
        public readonly ?int $remainingMs = null,

        public readonly ?int $subjectId = null,
        public readonly ?int $taskId = null
    ) {
    }
}
