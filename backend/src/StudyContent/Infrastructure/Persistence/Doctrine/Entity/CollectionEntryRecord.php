<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'study_collection_entries')]
#[ORM\UniqueConstraint(name: 'uniq_collection_entry_content', columns: ['collection_id', 'content_item_id'])]
#[ORM\UniqueConstraint(name: 'uniq_collection_entry_position', columns: ['collection_id', 'position'])]
final class CollectionEntryRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CollectionRecord::class, inversedBy: 'entries')]
    #[ORM\JoinColumn(name: 'collection_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public CollectionRecord $collection;

    #[ORM\Column(name: 'content_item_id', type: 'string', length: 36)]
    public string $contentItemId;

    #[ORM\Column(name: 'position', type: 'integer')]
    public int $position;

    #[ORM\Column(name: 'curation_note', type: 'text', nullable: true)]
    public ?string $curationNote = null;

    #[ORM\Column(name: 'added_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $addedAt;
}
