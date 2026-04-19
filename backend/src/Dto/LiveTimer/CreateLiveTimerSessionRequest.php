<?php

namespace App\Dto\LiveTimer;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateLiveTimerSessionRequest
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
