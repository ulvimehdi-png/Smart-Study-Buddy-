<?php

namespace App\Feature\LiveTimerSession\Service;

final class TimeService
{
    public function durationToMs(array $duration): int
    {
        $minutes = max(0, (int)($duration['minutes'] ?? 0));
        $seconds = max(0, (int)($duration['seconds'] ?? 0));

        return max(1000, (($minutes * 60) + $seconds) * 1000);
    }

    public function nowMs(): int
    {
        return (int)floor(microtime(true) * 1000);
    }

    public function isoFromMs(int $ms): string
    {
        return gmdate(DATE_ATOM, (int)floor($ms / 1000));
    }

    public function isoToMs(string $iso): int
    {
        return ((int)strtotime($iso)) * 1000;
    }
}

