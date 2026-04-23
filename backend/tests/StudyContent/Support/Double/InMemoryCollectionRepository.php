<?php

declare(strict_types=1);

namespace App\Tests\StudyContent\Support\Double;

use App\StudyContent\Domain\Model\Collection\Collection;
use App\StudyContent\Domain\Model\Collection\CollectionId;
use App\StudyContent\Domain\Repository\CollectionRepository;

final class InMemoryCollectionRepository implements CollectionRepository
{
    /** @var array<string, Collection> */
    private array $collections = [];

    public function save(Collection $collection): void
    {
        $this->collections[$collection->id()->toString()] = $collection;
    }

    public function get(CollectionId $id): Collection
    {
        $collection = $this->find($id);

        if ($collection === null) {
            throw new \RuntimeException(sprintf('Collection "%s" not found.', $id->toString()));
        }

        return $collection;
    }

    public function find(CollectionId $id): ?Collection
    {
        return $this->collections[$id->toString()] ?? null;
    }
}
