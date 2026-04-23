<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'study_content_relationships')]
#[ORM\UniqueConstraint(name: 'uniq_study_content_relationship', columns: ['content_item_id', 'relationship_type', 'target_content_item_id'])]
final class ContentRelationshipRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ContentItemRecord::class, inversedBy: 'relationships')]
    #[ORM\JoinColumn(name: 'content_item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public ContentItemRecord $contentItem;

    #[ORM\Column(name: 'relationship_type', type: 'string', length: 40)]
    public string $relationshipType;

    #[ORM\Column(name: 'target_content_item_id', type: 'string', length: 36)]
    public string $targetContentItemId;

    #[ORM\Column(name: 'recorded_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $recordedAt;
}
