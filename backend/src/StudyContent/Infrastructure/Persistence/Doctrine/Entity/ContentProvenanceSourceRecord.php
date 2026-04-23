<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'study_content_provenance_sources')]
final class ContentProvenanceSourceRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ContentItemRecord::class, inversedBy: 'provenanceSources')]
    #[ORM\JoinColumn(name: 'content_item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public ContentItemRecord $contentItem;

    #[ORM\Column(name: 'source_content_item_id', type: 'string', length: 36, nullable: true)]
    public ?string $sourceContentItemId = null;

    #[ORM\Column(name: 'source_reference_type', type: 'string', length: 50, nullable: true)]
    public ?string $sourceReferenceType = null;

    #[ORM\Column(name: 'source_reference_value', type: 'text', nullable: true)]
    public ?string $sourceReferenceValue = null;

    #[ORM\Column(name: 'derivation_method', type: 'string', length: 40)]
    public string $derivationMethod;

    #[ORM\Column(name: 'recorded_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $recordedAt;
}
