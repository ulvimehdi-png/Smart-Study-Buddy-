<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Persistence\Doctrine\Mapper;

use App\StudyContent\Domain\Model\Collection\Collection;
use App\StudyContent\Infrastructure\Persistence\Doctrine\Entity\CollectionRecord;

interface CollectionMapper
{
    public function toDomain(CollectionRecord $record): Collection;

    public function toRecord(
        Collection        $aggregate,
        ?CollectionRecord $existingRecord = null
    ): CollectionRecord;
}
