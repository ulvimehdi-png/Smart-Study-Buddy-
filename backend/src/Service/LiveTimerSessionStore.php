<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;

final class LiveTimerSessionStore
{
    public function __construct(
        private CacheItemPoolInterface $liveTimerCache,
    ) {
    }

    public function create(array $data): array
    {
        $id = bin2hex(random_bytes(16));

        $workDurationMs = $this->durationToMs($data['work'] ?? ['minutes' => 25, 'seconds' => 0]);
        $breakDurationMs = $this->durationToMs($data['break'] ?? ['minutes' => 5, 'seconds' => 0]);

        $state = [
            'id' => $id,
            'status' => 'idle',
            'phase' => 'focus',
            'workDurationMs' => $workDurationMs,
            'breakDurationMs' => $breakDurationMs,
            'phaseStartedAt' => null,
            'phaseEndsAt' => null,
            'pausedRemainingMs' => $workDurationMs,
            'subjectId' => $data['subjectId'] ?? null,
            'taskId' => $data['taskId'] ?? null,
            'updatedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];

        $item = $this->liveTimerCache->getItem($this->key($id));
        $item->set($state);
        $this->liveTimerCache->save($item);

        return $state;
    }

    public function get(string $id): ?array
    {
        $item = $this->liveTimerCache->getItem($this->key($id));

        if (!$item->isHit()) {
            return null;
        }

        $value = $item->get();

        return is_array($value) ? $value : null;
    }

    public function delete(string $id): void
    {
        $this->liveTimerCache->deleteItem($this->key($id));
    }

    private function key(string $id): string
    {
        return 'live_timer_session_' . $id;
    }

    private function durationToMs(array $duration): int
    {
        $minutes = max(0, (int) ($duration['minutes'] ?? 0));
        $seconds = max(0, (int) ($duration['seconds'] ?? 0));

        return max(1000, (($minutes * 60) + $seconds) * 1000);
    }
}
