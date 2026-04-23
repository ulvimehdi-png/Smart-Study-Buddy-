<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\Collection;

use App\StudyContent\Domain\Event\CollectionPublished;
use App\StudyContent\Domain\Event\ContentAddedToCollection;
use App\StudyContent\Domain\Exception\CollectionMembershipNotAllowed;
use App\StudyContent\Domain\Exception\InvalidCollectionOperation;
use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\StudyContent\Domain\Model\ContentItem\OwnerId;
use App\StudyContent\Domain\Model\ContentItem\SharingScope;
use App\StudyContent\Domain\Shared\RecordsDomainEvents;
use DateTimeImmutable;

final class Collection
{
    use RecordsDomainEvents;

    private CollectionId $id;
    private CollectionTitle $title;
    private OwnerId $ownerId;
    private SharingScope $scope;
    private CollectionStatus $status;
    /** @var list<CollectionEntry> */
    private array $entries = [];
    /** @var list<CollectionPublicationRecord> */
    private array $publicationHistory = [];
    private ?DateTimeImmutable $archivedAt = null;

    private function __construct(
        CollectionId     $id,
        CollectionTitle  $title,
        OwnerId          $ownerId,
        SharingScope     $scope,
        CollectionStatus $status,
    )
    {
        $this->id = $id;
        $this->title = $title;
        $this->ownerId = $ownerId;
        $this->scope = $scope;
        $this->status = $status;
    }

    public static function create(
        CollectionId    $id,
        CollectionTitle $title,
        OwnerId         $ownerId,
        SharingScope    $scope,
    ): self
    {
        return new self(id: $id, title: $title, ownerId: $ownerId, scope: $scope,
            status: CollectionStatus::DRAFT,);
    }

    public function rename(CollectionTitle $title): void
    {
        if ($this->status === CollectionStatus::ARCHIVED) {
            throw InvalidCollectionOperation::fromStatus($this->status, 'rename');
        }

        $this->title = $title;
    }

    public function addEntry(
        ContentItemId      $contentItemId,
        ?string            $curationNote = null,
        ?DateTimeImmutable $now = null,
    ): void
    {
        $now ??= new DateTimeImmutable();

        if ($this->status === CollectionStatus::ARCHIVED) {
            throw CollectionMembershipNotAllowed::archivedCollection();
        }

        foreach ($this->entries as $entry) {
            if ($entry->sameContent($contentItemId)) {
                throw CollectionMembershipNotAllowed::duplicateContent($contentItemId->toString());
            }
        }

        $position = new CollectionEntryPosition(count($this->entries) + 1,);

        $this->entries[] = new CollectionEntry(contentItemId: $contentItemId,
            position: $position, addedAt: $now,
            curationNote: $curationNote,);

        $this->record(new ContentAddedToCollection($this->id, $contentItemId, $now,));
    }

    public function removeEntry(ContentItemId $contentItemId): void
    {
        if ($this->status === CollectionStatus::ARCHIVED) {
            throw CollectionMembershipNotAllowed::archivedCollection();
        }

        $filtered = [];
        $removed = false;

        foreach ($this->entries as $entry) {
            if ($entry->sameContent($contentItemId)) {
                $removed = true;
                continue;
            }

            $filtered[] = $entry;
        }

        if (!$removed) {
            return;
        }

        $this->entries = [];
        $position = 1;

        foreach ($filtered as $entry) {
            $entry->moveTo(new CollectionEntryPosition($position,));
            $this->entries[] = $entry;
            $position++;
        }
    }

    public function reorder(
        ContentItemId           $contentItemId,
        CollectionEntryPosition $newPosition,
    ): void
    {
        if ($this->status === CollectionStatus::ARCHIVED) {
            throw CollectionMembershipNotAllowed::archivedCollection();
        }

        $count = count($this->entries);

        if ($count === 0) {
            return;
        }

        $targetIndex = null;

        foreach ($this->entries as $index => $entry) {
            if ($entry->sameContent($contentItemId)) {
                $targetIndex = $index;
                break;
            }
        }

        if ($targetIndex === null) {
            return;
        }

        $entry = $this->entries[$targetIndex];
        unset($this->entries[$targetIndex],);
        $this->entries = array_values($this->entries);

        $insertIndex = min(max($newPosition->toInt() - 1, 0), count($this->entries));
        array_splice($this->entries, $insertIndex, 0, [$entry]);

        foreach ($this->entries as $index => $currentEntry) {
            $currentEntry->moveTo(new CollectionEntryPosition($index + 1,));
        }
    }

    public function publish(
        string             $publishedBy,
        ?DateTimeImmutable $now = null,
    ): void
    {
        $now ??= new DateTimeImmutable();

        if ($this->status === CollectionStatus::ARCHIVED) {
            throw InvalidCollectionOperation::fromStatus($this->status, 'publish');
        }

        if ($this->entries === []) {
            throw CollectionMembershipNotAllowed::emptyPublishedCollection();
        }

        $this->status = CollectionStatus::PUBLISHED;

        $this->publicationHistory[] = new CollectionPublicationRecord(scope: $this->scope,
            publishedBy: $publishedBy,
            publishedAt: $now,);

        $this->record(new CollectionPublished($this->id, $this->scope, $now,));
    }

    public function archive(?DateTimeImmutable $now = null): void
    {
        $now ??= new DateTimeImmutable();

        if ($this->status === CollectionStatus::ARCHIVED) {
            return;
        }

        $this->status = CollectionStatus::ARCHIVED;
        $this->archivedAt = $now;
    }

    public function id(): CollectionId
    {
        return $this->id;
    }

    public function title(): CollectionTitle
    {
        return $this->title;
    }

    public function ownerId(): OwnerId
    {
        return $this->ownerId;
    }

    public function scope(): SharingScope
    {
        return $this->scope;
    }

    public function status(): CollectionStatus
    {
        return $this->status;
    }

    /**
     * @return list<CollectionEntry>
     */
    public function entries(): array
    {
        return $this->entries;
    }

    /**
     * @return list<CollectionPublicationRecord>
     */
    public function publicationHistory(): array
    {
        return $this->publicationHistory;
    }

    /**
     * @param list<CollectionEntry> $entries
     * @param list<CollectionPublicationRecord> $publicationHistory
     */
    public static function reconstitute(
        CollectionId        $id,
        CollectionTitle     $title,
        OwnerId             $ownerId,
        SharingScope        $scope,
        CollectionStatus    $status,
        array               $entries,
        array               $publicationHistory,
        ?\DateTimeImmutable $archivedAt
    ): self
    {
        $self = new self(
            id: $id,
            title: $title,
            ownerId: $ownerId,
            scope: $scope,
            status: $status
        );

        $self->entries = $entries;
        $self->publicationHistory = $publicationHistory;
        $self->archivedAt = $archivedAt;

        return $self;
    }

    public function archivedAt(): ?\DateTimeImmutable
    {
        return $this->archivedAt;
    }
}
