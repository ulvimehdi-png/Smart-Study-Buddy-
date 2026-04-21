<?php

namespace App\Feature\LiveTimerSession\Service;

use Psr\Cache\CacheItemPoolInterface;

final class Store
{
    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly StateHydrator          $hydrator,
    )
    {
    }

    public function get(string $id): ?State
    {
        $item = $this->cache->getItem($this->key($id));

        if (!$item->isHit()) {
            return null;
        }

        $data = $item->get();

        if (!is_array($data)) {
            return null;
        }

        return $this->hydrator->fromArray($data);
    }

    private function key(string $id): string
    {
        return 'live_timer_session_' . $id;
    }

    public function save(State $state): State
    {
        $item = $this->cache->getItem($this->key($state->id));
        $item->set($this->hydrator->toArray($state));

        $this->cache->save($item);

        return $state;
    }

    public function delete(string $id): void
    {
        $this->cache->deleteItem($this->key($id));
    }
}
