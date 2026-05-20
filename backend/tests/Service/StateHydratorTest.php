<?php

namespace App\Tests\LiveTimerSession\Service;

use App\LiveTimerSession\Enum\SessionPhase;
use App\LiveTimerSession\Enum\SessionStatus;
use App\LiveTimerSession\Service\State;
use App\LiveTimerSession\Service\StateHydrator;
use PHPUnit\Framework\TestCase;

final class StateHydratorTest extends TestCase
{
    public function testToArraySerializesState(): void
    {
        $state = new State(
            id: 'abc123',
            status: SessionStatus::RUNNING,
            phase: SessionPhase::FOCUS,
            workDurationMs: 1_500_000,
            breakDurationMs: 300_000,
            phaseEndsAt: '2026-05-20T12:00:00+00:00',
            remainingMs: null,
            startedAt: '2026-05-20T11:35:00+00:00',
            accumulatedFocusMs: 120_000,
            accumulatedBreakMs: 30_000,
            subjectId: 10,
            taskId: 20,
            updatedAt: '2026-05-20T11:40:00+00:00',
            ownerUserId: 7,
        );

        $data = (new StateHydrator())->toArray($state);

        self::assertSame('abc123', $data['id']);
        self::assertSame(SessionStatus::RUNNING->value, $data['status']);
        self::assertSame(SessionPhase::FOCUS->value, $data['phase']);
        self::assertSame(1_500_000, $data['workDurationMs']);
        self::assertSame(300_000, $data['breakDurationMs']);
        self::assertSame(7, $data['userOwnerId']);
    }

    public function testFromArrayHydratesState(): void
    {
        $data = [
            'id' => 'abc123',
            'status' => SessionStatus::PAUSED->value,
            'phase' => SessionPhase::BREAK->value,
            'workDurationMs' => 1_500_000,
            'breakDurationMs' => 300_000,
            'phaseEndsAt' => null,
            'remainingMs' => 200_000,
            'startedAt' => '2026-05-20T11:35:00+00:00',
            'accumulatedFocusMs' => 120_000,
            'accumulatedBreakMs' => 30_000,
            'subjectId' => 10,
            'taskId' => 20,
            'updatedAt' => '2026-05-20T11:40:00+00:00',
            'userOwnerId' => 7,
        ];

        $state = (new StateHydrator())->fromArray($data);

        self::assertSame('abc123', $state->id);
        self::assertSame(SessionStatus::PAUSED, $state->status);
        self::assertSame(SessionPhase::BREAK, $state->phase);
        self::assertSame(1_500_000, $state->workDurationMs);
        self::assertSame(300_000, $state->breakDurationMs);
        self::assertSame(200_000, $state->remainingMs);
        self::assertSame(7, $state->ownerUserId);
    }

    public function testRoundTripPreservesState(): void
    {
        $hydrator = new StateHydrator();

        $state = new State(
            id: 'roundtrip',
            status: SessionStatus::IDLE,
            phase: SessionPhase::FOCUS,
            workDurationMs: 2_000,
            breakDurationMs: 1_000,
            phaseEndsAt: null,
            remainingMs: 2_000,
            startedAt: null,
            accumulatedFocusMs: 0,
            accumulatedBreakMs: 0,
            subjectId: null,
            taskId: null,
            updatedAt: '2026-05-20T11:40:00+00:00',
            ownerUserId: 99,
        );

        $rehydrated = $hydrator->fromArray($hydrator->toArray($state));

        self::assertEquals($state, $rehydrated);
    }
}
