<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Persistence\Doctrine\Mapper;

use App\StudyContent\Domain\Model\ContentItem\ContentItem;
use App\StudyContent\Infrastructure\Persistence\Doctrine\Entity\ContentItemRecord;

interface ContentItemMapper
{
    public function toDomain(ContentItemRecord $record): ContentItem;

    public function toRecord(
        ContentItem $aggregate,
        ?ContentItemRecord $existingRecord = null
    ): ContentItemRecord;
}
