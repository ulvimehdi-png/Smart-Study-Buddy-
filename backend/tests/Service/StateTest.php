<?php

namespace App\Tests\LiveTimerSession\Service;

use App\LiveTimerSession\Enum\SessionPhase;
use App\LiveTimerSession\Enum\SessionStatus;
use App\LiveTimerSession\Service\State;
use App\LiveTimerSession\Service\TimeService;
use PHPUnit\Framework\TestCase;

final class StateTest extends TestCase
{
    public function testCreateIdleState(): void
    {
        $time = new TimeService();

        $state = State::create(
            id: 'timer1',
            ownerUserId: 5,
            workDurationMs: 1_500_000,
            breakDurationMs: 300_000,
            time: $time,
        );

        self::assertSame('timer1', $state->id);
        self::assertSame(5, $state->ownerUserId);
        self::assertSame(SessionStatus::IDLE, $state->status);
        self::assertSame(SessionPhase::FOCUS, $state->phase);
        self::assertNull($state->phaseEndsAt);
        self::assertSame(1_500_000, $state->remainingMs);
        self::assertNull($state->startedAt);
    }

    public function testStartMovesPausedOrIdleStateToRunning(): void
    {
        $time = new TimeService();

        $state = State::create(
            id: 'timer1',
            ownerUserId: 5,
            workDurationMs: 60_000,
            breakDurationMs: 10_000,
            time: $time,
        );

        $state->start($time);

        self::assertSame(SessionStatus::RUNNING, $state->status);
        self::assertNotNull($state->phaseEndsAt);
        self::assertNull($state->remainingMs);
        self::assertNotNull($state->startedAt);
        self::assertTrue($state->isRunning());
    }

    public function testStartDoesNothingWhenAlreadyRunning(): void
    {
        $time = new TimeService();

        $state = State::create(
            id: 'timer1',
            ownerUserId: 5,
            workDurationMs: 60_000,
            breakDurationMs: 10_000,
            time: $time,
            status: SessionStatus::RUNNING,
        );

        $originalPhaseEndsAt = $state->phaseEndsAt;

        $state->start($time);

        self::assertSame(SessionStatus::RUNNING, $state->status);
        self::assertSame($originalPhaseEndsAt, $state->phaseEndsAt);
    }

    public function testPauseStoresRemainingTime(): void
    {
        $time = new TimeService();

        $state = State::create(
            id: 'timer1',
            ownerUserId: 5,
            workDurationMs: 60_000,
            breakDurationMs: 10_000,
            time: $time,
        );

        $state->start($time);
        $state->pause($time);

        self::assertSame(SessionStatus::PAUSED, $state->status);
        self::assertNull($state->phaseEndsAt);
        self::assertNotNull($state->remainingMs);
        self::assertGreaterThanOrEqual(0, $state->remainingMs);
        self::assertLessThanOrEqual(60_000, $state->remainingMs);
    }

    public function testPauseDoesNothingWhenNotRunning(): void
    {
        $time = new TimeService();

        $state = State::create(
            id: 'timer1',
            ownerUserId: 5,
            workDurationMs: 60_000,
            breakDurationMs: 10_000,
            time: $time,
        );

        $state->pause($time);

        self::assertSame(SessionStatus::IDLE, $state->status);
        self::assertSame(60_000, $state->remainingMs);
        self::assertNull($state->phaseEndsAt);
    }

    public function testSkipPhaseSwitchesToOtherPhaseAndPauses(): void
    {
        $time = new TimeService();

        $state = State::create(
            id: 'timer1',
            ownerUserId: 5,
            workDurationMs: 60_000,
            breakDurationMs: 10_000,
            time: $time,
        );

        $state->skipPhase();

        self::assertSame(SessionPhase::BREAK, $state->phase);
        self::assertSame(SessionStatus::PAUSED, $state->status);
        self::assertSame(10_000, $state->remainingMs);
        self::assertNull($state->phaseEndsAt);
    }

    public function testResetPhaseKeepsPhaseAndRestoresRemainingDuration(): void
    {
        $time = new TimeService();

        $state = State::create(
            id: 'timer1',
            ownerUserId: 5,
            workDurationMs: 60_000,
            breakDurationMs: 10_000,
            time: $time,
            phase: SessionPhase::BREAK,
            remainingMs: 3_000,
        );

        $state->resetPhase();

        self::assertSame(SessionPhase::BREAK, $state->phase);
        self::assertSame(SessionStatus::PAUSED, $state->status);
        self::assertSame(10_000, $state->remainingMs);
        self::assertNull($state->phaseEndsAt);
    }

    public function testGetTotalDurationsAddsElapsedFocusTime(): void
    {
        $time = new TimeService();
        $nowMs = $time->nowMs();

        $state = new State(
            id: 'timer1',
            status: SessionStatus::RUNNING,
            phase: SessionPhase::FOCUS,
            workDurationMs: 60_000,
            breakDurationMs: 10_000,
            phaseEndsAt: $time->isoFromMs($nowMs + 40_000),
            remainingMs: null,
            startedAt: $time->isoFromMs($nowMs - 20_000),
            accumulatedFocusMs: 5_000,
            accumulatedBreakMs: 2_000,
            subjectId: null,
            taskId: null,
            updatedAt: $time->isoFromMs($nowMs),
            ownerUserId: 5,
        );

        [$focusMs, $breakMs] = $state->getTotalDurations($time);

        self::assertGreaterThanOrEqual(24_000, $focusMs);
        self::assertLessThanOrEqual(27_000, $focusMs);
        self::assertSame(2_000, $breakMs);
    }
}
