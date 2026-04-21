<?php

namespace App\Feature\LiveTimerSession\Dto\Command;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class StopSession
{
    public function __construct(
        #[Assert\Length(max: 255)]
        public ?string $sessionNote = null,

        #[Assert\GreaterThanOrEqual(0)]
        public int     $distractionCount = 0,

        #[Assert\Choice(choices: ['completed', 'cancelled'])]
        public string  $finalStatus = 'completed',
    )
    {
    }
}
