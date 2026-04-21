<?php

namespace App\Feature\LiveTimerSession\Service;

use App\Feature\LiveTimerSession\Enum\SessionPhase;
use App\Feature\LiveTimerSession\Enum\SessionStatus;

final class Projector
{
    public function __construct(
        private readonly TimeService $time,
    )
    {
    }

    public function project(State $state, ?int $nowMs = null): array
    {
        $nowMs ??= $this->time->nowMs();

        $effective = $this->resolve($state, $nowMs);

        return [
            'id' => $effective->id,
            'topic' => self::topicForId($effective->id),
            'state' => [
                'id' => $effective->id,
                'status' => $effective->status->value,
                'phase' => $effective->phase->value,
                'workDurationMs' => $effective->workDurationMs,
                'breakDurationMs' => $effective->breakDurationMs,
                'phaseEndsAt' => $effective->isRunning()
                    ? $effective->phaseEndsAt
                    : null,
                'remainingMs' => $effective->isRunning()
                    ? null
                    : max(
                        0,
                        $effective->remainingMs ?? $effective->currentPhaseDuration()
                    ),
                'startedAt' => $effective->startedAt,
                'accumulatedFocusMs' => $effective->accumulatedFocusMs,
                'accumulatedBreakMs' => $effective->accumulatedBreakMs,
                'subjectId' => $effective->subjectId,
                'taskId' => $effective->taskId,
                'updatedAt' => $effective->updatedAt,
            ],
        ];
    }

    public function resolve(State $state, ?int $nowMs = null): State
    {
        $nowMs ??= $this->time->nowMs();

        if (($state->status ?? SessionStatus::IDLE) !== SessionStatus::RUNNING || empty($state->phaseEndsAt)) {
            return $state;
        }

        $resolved = clone $state;
        $endMs = $this->time->isoToMs($resolved->phaseEndsAt);

        if ($nowMs < $endMs) {
            return $resolved;
        }

        $overflowMs = $nowMs - $endMs;

        $this->addFullPhaseDuration($resolved, $resolved->phase);

        $currentPhase = $resolved->phase === SessionPhase::FOCUS
            ? SessionPhase::BREAK
            : SessionPhase::FOCUS;

        while (true) {
            $durationMs = $this->phaseDuration($resolved, $currentPhase);

            if ($overflowMs < $durationMs) {
                $resolved->phase = $currentPhase;
                $resolved->phaseEndsAt = $this->time->isoFromMs(
                    $nowMs + ($durationMs - $overflowMs)
                );
                $resolved->remainingMs = null;

                return $resolved;
            }

            $this->addFullPhaseDuration($resolved, $currentPhase);
            $overflowMs -= $durationMs;

            $currentPhase = $currentPhase === SessionPhase::FOCUS
                ? SessionPhase::BREAK
                : SessionPhase::FOCUS;
        }
    }

    private function addFullPhaseDuration(State $state, SessionPhase $phase): void
    {
        if ($phase === SessionPhase::BREAK) {
            $state->accumulatedBreakMs = ($state->accumulatedBreakMs ?? 0) + $state->breakDurationMs;
            return;
        }

        $state->accumulatedFocusMs = ($state->accumulatedFocusMs ?? 0) + $state->workDurationMs;
    }

    private function phaseDuration(State $state, SessionPhase $phase): int
    {
        return $phase === SessionPhase::BREAK
            ? $state->breakDurationMs
            : $state->workDurationMs;
    }

    public static function topicForId(string $id): string
    {
        return sprintf('http://localhost/live-timer-sessions/%s', $id);
    }
}
