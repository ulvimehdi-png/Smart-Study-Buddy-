<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'study_content_review_records')]
final class ContentReviewRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ContentItemRecord::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(name: 'content_item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public ContentItemRecord $contentItem;

    #[ORM\Column(name: 'reviewer_id', type: 'string', length: 36)]
    public string $reviewerId;

    #[ORM\Column(name: 'outcome', type: 'string', length: 40)]
    public string $outcome;

    #[ORM\Column(name: 'reason', type: 'text')]
    public string $reason;

    #[ORM\Column(name: 'reviewed_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $reviewedAt;
}
