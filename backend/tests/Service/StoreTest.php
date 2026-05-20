<?php

namespace App\Tests\LiveTimerSession\Service;

use App\LiveTimerSession\Enum\SessionPhase;
use App\LiveTimerSession\Enum\SessionStatus;
use App\LiveTimerSession\Service\State;
use App\LiveTimerSession\Service\StateHydrator;
use App\LiveTimerSession\Service\Store;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class StoreTest extends TestCase
{
    private ArrayAdapter $cache;
    private Store $store;

    protected function setUp(): void
    {
        $this->cache = new ArrayAdapter();
        $this->store = new Store($this->cache, new StateHydrator());
    }

    public function testGetReturnsNullWhenItemDoesNotExist(): void
    {
        self::assertNull($this->store->get('missing'));
    }

    public function testSaveAndGetRoundTrip(): void
    {
        $state = $this->state('timer1');

        $saved = $this->store->save($state);
        $loaded = $this->store->get('timer1');

        self::assertSame($state, $saved);
        self::assertEquals($state, $loaded);
    }

    public function testGetReturnsNullWhenCachedValueIsNotArray(): void
    {
        $item = $this->cache->getItem('live_timer_session_timer1');
        $item->set('not-an-array');

        $this->cache->save($item);

        self::assertNull($this->store->get('timer1'));
    }

    public function testDeleteRemovesState(): void
    {
        $this->store->save($this->state('timer1'));

        self::assertNotNull($this->store->get('timer1'));

        $this->store->delete('timer1');

        self::assertNull($this->store->get('timer1'));
    }

    private function state(string $id): State
    {
        return new State(
            id: $id,
            status: SessionStatus::PAUSED,
            phase: SessionPhase::FOCUS,
            workDurationMs: 60_000,
            breakDurationMs: 10_000,
            phaseEndsAt: null,
            remainingMs: 30_000,
            startedAt: null,
            accumulatedFocusMs: 0,
            accumulatedBreakMs: 0,
            subjectId: null,
            taskId: null,
            updatedAt: '2026-05-20T12:00:00+00:00',
            ownerUserId: 1,
        );
    }
}
