<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Persistence\Doctrine\Mapper;

use App\StudyContent\Domain\Model\Collection\Collection;
use App\StudyContent\Domain\Model\Collection\CollectionEntry;
use App\StudyContent\Domain\Model\Collection\CollectionEntryPosition;
use App\StudyContent\Domain\Model\Collection\CollectionId;
use App\StudyContent\Domain\Model\Collection\CollectionPublicationRecord;
use App\StudyContent\Domain\Model\Collection\CollectionStatus;
use App\StudyContent\Domain\Model\Collection\CollectionTitle;
use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\StudyContent\Domain\Model\ContentItem\OwnerId;
use App\StudyContent\Domain\Model\ContentItem\SharingScope;
use App\StudyContent\Infrastructure\Persistence\Doctrine\Entity\CollectionEntryRecord;
use App\StudyContent\Infrastructure\Persistence\Doctrine\Entity\CollectionPublicationHistoryRecord;
use App\StudyContent\Infrastructure\Persistence\Doctrine\Entity\CollectionRecord;
use Doctrine\Common\Collections\ArrayCollection;

final class DoctrineCollectionMapper implements CollectionMapper
{
    public function toDomain(CollectionRecord $record): Collection
    {
        $entries = [];
        foreach ($record->entries as $row) {
            $entries[] = new CollectionEntry(
                contentItemId: ContentItemId::fromString($row->contentItemId),
                position: new CollectionEntryPosition($row->position),
                addedAt: $row->addedAt,
                curationNote: $row->curationNote
            );
        }

        $publications = [];
        foreach ($record->publications as $row) {
            $publications[] = new CollectionPublicationRecord(
                scope: SharingScope::from($row->scope),
                publishedBy: $row->publishedBy,
                publishedAt: $row->publishedAt
            );
        }

        return Collection::reconstitute(
            id: CollectionId::fromString($record->id),
            title: new CollectionTitle($record->title),
            ownerId: new OwnerId($record->ownerId),
            scope: SharingScope::from($record->scope),
            status: CollectionStatus::from($record->status),
            entries: $entries,
            publicationHistory: $publications,
            archivedAt: $record->archivedAt
        );
    }

    public function toRecord(
        Collection $aggregate,
        ?CollectionRecord $existingRecord = null
    ): CollectionRecord {
        $record = $existingRecord ?? new CollectionRecord();

        $record->id = $aggregate->id()->toString();
        $record->ownerId = $aggregate->ownerId()->toString();
        $record->title = $aggregate->title()->toString();
        $record->scope = $aggregate->scope()->value;
        $record->status = $aggregate->status()->value;
        $record->archivedAt = $aggregate->archivedAt();

        $now = new \DateTimeImmutable();
        if (!isset($record->createdAt)) {
            $record->createdAt = $now;
        }
        $record->updatedAt = $now;

        $record->entries = new ArrayCollection();
        foreach ($aggregate->entries() as $entry) {
            $row = new CollectionEntryRecord();
            $row->collection = $record;
            $row->contentItemId = $entry->contentItemId()->toString();
            $row->position = $entry->position()->toInt();
            $row->curationNote = $entry->curationNote();
            $row->addedAt = $entry->addedAt();

            $record->entries->add($row);
        }

        $record->publications = new ArrayCollection();
        foreach ($aggregate->publicationHistory() as $publication) {
            $row = new CollectionPublicationHistoryRecord();
            $row->collection = $record;
            $row->scope = $publication->scope()->value;
            $row->publishedBy = $publication->publishedBy();
            $row->publishedAt = $publication->publishedAt();

            $record->publications->add($row);
        }

        return $record;
    }
}
