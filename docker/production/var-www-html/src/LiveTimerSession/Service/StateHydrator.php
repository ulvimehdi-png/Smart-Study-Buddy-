<?php

namespace App\LiveTimerSession\Service;

use App\LiveTimerSession\Enum\SessionPhase;
use App\LiveTimerSession\Enum\SessionStatus;

final class StateHydrator
{
    public function fromArray(array $data): State
    {
        return new State(
            id: $data['id'],
            status: SessionStatus::from($data['status']),
            phase: SessionPhase::from($data['phase']),
            workDurationMs: (int) $data['workDurationMs'],
            breakDurationMs: (int) $data['breakDurationMs'],
            phaseEndsAt: $data['phaseEndsAt'] ?? null,
            remainingMs: isset($data['remainingMs']) ? (int) $data['remainingMs'] : null,
            startedAt: $data['startedAt'] ?? null,
            accumulatedFocusMs: (int) ($data['accumulatedFocusMs'] ?? 0),
            accumulatedBreakMs: (int) ($data['accumulatedBreakMs'] ?? 0),
            subjectId: $data['subjectId'] ?? null,
            taskId: $data['taskId'] ?? null,
            updatedAt: $data['updatedAt'],
            ownerUserId: $data['userOwnerId']
        );
    }

    public function toArray(State $state): array
    {
        return [
            'id' => $state->id,
            'status' => $state->status->value,
            'phase' => $state->phase->value,
            'workDurationMs' => $state->workDurationMs,
            'breakDurationMs' => $state->breakDurationMs,
            'phaseEndsAt' => $state->phaseEndsAt,
            'remainingMs' => $state->remainingMs,
            'startedAt' => $state->startedAt,
            'accumulatedFocusMs' => $state->accumulatedFocusMs,
            'accumulatedBreakMs' => $state->accumulatedBreakMs,
            'subjectId' => $state->subjectId,
            'taskId' => $state->taskId,
            'updatedAt' => $state->updatedAt,
            'userOwnerId' => $state->ownerUserId,
        ];
    }
}
