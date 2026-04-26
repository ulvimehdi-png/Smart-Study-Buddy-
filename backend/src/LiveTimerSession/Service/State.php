<?php

namespace App\LiveTimerSession\Service;

use App\LiveTimerSession\DTO\DurationDto;
use App\LiveTimerSession\Enum\SessionPhase;
use App\LiveTimerSession\Enum\SessionStatus;

final class State
{
    public function __construct(
        public string        $id,
        public SessionStatus $status,
        public SessionPhase  $phase,
        public int           $workDurationMs,
        public int           $breakDurationMs,
        public ?string       $phaseEndsAt,
        public ?int          $remainingMs,
        public ?string       $startedAt,
        public int           $accumulatedFocusMs,
        public int           $accumulatedBreakMs,
        public ?int          $subjectId,
        public ?int          $taskId,
        public string        $updatedAt,
        public int           $ownerUserId,
    )
    {
    }

    public static function create(
        string $id,
        int    $ownerUserId,
        int    $workDurationMs,
        int    $breakDurationMs,
        ?int   $subjectId = null,
        ?int   $taskId = null,
    ): self
    {
        $now = new \DateTimeImmutable()->format(DATE_ATOM);

        return new self(
            id: $id,
            status: SessionStatus::IDLE,
            phase: SessionPhase::FOCUS,
            workDurationMs: $workDurationMs,
            breakDurationMs: $breakDurationMs,
            phaseEndsAt: null,
            remainingMs: $workDurationMs,
            startedAt: null,
            accumulatedFocusMs: 0,
            accumulatedBreakMs: 0,
            subjectId: $subjectId,
            taskId: $taskId,
            updatedAt: $now,
            ownerUserId: $ownerUserId,
        );
    }

    /*
     *
     * CORE ACTIONS
     *
    */

    public function start(TimeService $time): void
    {
        if ($this->isRunning()) {
            return;
        }

        $remainingMs = max(
            1000,
            $this->remainingMs ?? $this->currentPhaseDuration()
        );

        $this->status = SessionStatus::RUNNING;
        $this->phaseEndsAt = $time->isoFromMs($time->nowMs() + $remainingMs);
        $this->remainingMs = null;

        $now = new \DateTimeImmutable()->format(DATE_ATOM);

        $this->startedAt ??= $now;
        $this->updatedAt = $now;
    }

    public function pause(TimeService $time): void
    {
        if (!$this->isRunning() || !$this->phaseEndsAt) {
            return;
        }

        $remainingMs = max(
            0,
            $time->isoToMs($this->phaseEndsAt) - $time->nowMs()
        );

        $this->status = SessionStatus::PAUSED;
        $this->phaseEndsAt = null;
        $this->remainingMs = $remainingMs;

        $this->touch();
    }

    public function resume(TimeService $time): void
    {
        $this->start($time);
    }

    public function skipPhase(): void
    {
        $this->switchPhase();

        $this->status = SessionStatus::PAUSED;
        $this->phaseEndsAt = null;
        $this->remainingMs = $this->currentPhaseDuration();

        $this->touch();
    }

    public function resetPhase(): void
    {
        $this->status = SessionStatus::PAUSED;
        $this->phaseEndsAt = null;
        $this->remainingMs = $this->currentPhaseDuration();

        $this->touch();
    }

    public function changeConfig(TimeService $time, ?DurationDto $work, ?DurationDto $break): void
    {
        if ($work) {
            $this->workDurationMs = $time->durationToMs([
                'minutes' => $work->minutes,
                'seconds' => $work->seconds,
            ]);
        }

        if ($break) {
            $this->breakDurationMs = $time->durationToMs([
                'minutes' => $break->minutes,
                'seconds' => $break->seconds,
            ]);
        }

        $this->status = SessionStatus::PAUSED;
        $this->phaseEndsAt = null;
        $this->remainingMs = $this->currentPhaseDuration();

        $this->touch();
    }

    /*
     *
     * ADDITIONAL HELPERS
     *
     */

    public function isRunning(): bool
    {
        return $this->status === SessionStatus::RUNNING;
    }

    public function currentPhaseDuration(): int
    {
        return $this->phase === SessionPhase::BREAK
            ? $this->breakDurationMs
            : $this->workDurationMs;
    }

    public function switchPhase(): void
    {
        $this->phase = $this->phase === SessionPhase::FOCUS
            ? SessionPhase::BREAK
            : SessionPhase::FOCUS;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable()->format(DATE_ATOM);
    }



    /*
     *
     * TIME LOGIC
     *
     */

    public function getElapsedInCurrentPhase(TimeService $time): int
    {
        $durationMs = $this->currentPhaseDuration();

        if ($this->isRunning() && $this->phaseEndsAt) {
            $remainingMs = max(
                0,
                $time->isoToMs($this->phaseEndsAt) - $time->nowMs()
            );

            return max(0, $durationMs - $remainingMs);
        }

        $remainingMs = $this->remainingMs ?? $durationMs;

        return max(0, $durationMs - $remainingMs);
    }

    public function getTotalDurations(TimeService $time): array
    {
        $focusMs = $this->accumulatedFocusMs;
        $breakMs = $this->accumulatedBreakMs;

        $elapsed = $this->getElapsedInCurrentPhase($time);

        if ($this->phase === SessionPhase::BREAK) {
            $breakMs += $elapsed;
        } else {
            $focusMs += $elapsed;
        }

        return [$focusMs, $breakMs];
    }
}
