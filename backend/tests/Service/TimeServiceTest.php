<?php

namespace App\Tests\LiveTimerSession\Service;

use App\LiveTimerSession\Service\TimeService;
use PHPUnit\Framework\TestCase;

final class TimeServiceTest extends TestCase
{
    public function testDurationToMsConvertsMinutesAndSeconds(): void
    {
        $time = new TimeService();

        self::assertSame(90_000, $time->durationToMs([
            'minutes' => 1,
            'seconds' => 30,
        ]));
    }

    public function testDurationToMsClampsNegativeValuesAndUsesMinimumOneSecond(): void
    {
        $time = new TimeService();

        self::assertSame(1_000, $time->durationToMs([
            'minutes' => -10,
            'seconds' => -5,
        ]));

        self::assertSame(1_000, $time->durationToMs([]));
    }

    public function testIsoRoundTripUsesMilliseconds(): void
    {
        $time = new TimeService();

        $iso = $time->isoFromMs(1_700_000_000_000);

        self::assertSame(1_700_000_000_000, $time->isoToMs($iso));
    }

    public function testNowMsReturnsReasonableTimestamp(): void
    {
        $time = new TimeService();

        $before = (int) floor(microtime(true) * 1000);
        $now = $time->nowMs();
        $after = (int) floor(microtime(true) * 1000);

        self::assertGreaterThanOrEqual($before, $now);
        self::assertLessThanOrEqual($after + 10, $now);
    }
}
