<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'study_content_publication_records')]
final class ContentPublicationHistoryRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ContentItemRecord::class, inversedBy: 'publications')]
    #[ORM\JoinColumn(name: 'content_item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public ContentItemRecord $contentItem;

    #[ORM\Column(name: 'channel', type: 'string', length: 40)]
    public string $channel;

    #[ORM\Column(name: 'scope', type: 'string', length: 40)]
    public string $scope;

    #[ORM\Column(name: 'published_by', type: 'string', length: 36)]
    public string $publishedBy;

    #[ORM\Column(name: 'published_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $publishedAt;
}
