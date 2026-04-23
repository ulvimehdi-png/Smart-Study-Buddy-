<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Repository;

use App\StudyContent\Domain\Model\Collection\Collection;
use App\StudyContent\Domain\Model\Collection\CollectionId;

interface CollectionRepository
{
    public function save(Collection $collection): void;

    public function get(CollectionId $id): Collection;

    public function find(CollectionId $id): ?Collection;
}
