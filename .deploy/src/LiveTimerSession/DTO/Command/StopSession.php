<?php

namespace App\LiveTimerSession\DTO\Command;

use Symfony\Component\Validator\Constraints as Assert;

final class StopSession
{
    public function __construct(
        #[Assert\Length(max: 255)]
        public readonly ?string $sessionNote = null,

        #[Assert\GreaterThanOrEqual(0)]
        public readonly int $distractionCount = 0,

        #[Assert\Choice(choices: ['completed', 'cancelled'])]
        public readonly string $finalStatus = 'completed',
    ) {
    }
}
