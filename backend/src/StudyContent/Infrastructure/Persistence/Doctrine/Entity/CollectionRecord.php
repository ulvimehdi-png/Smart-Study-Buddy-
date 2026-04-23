<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'study_collections')]
final class CollectionRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(name: 'owner_id', type: 'string', length: 36)]
    public string $ownerId;

    #[ORM\Column(type: 'string', length: 200)]
    public string $title;

    #[ORM\Column(type: 'string', length: 40)]
    public string $scope;

    #[ORM\Column(type: 'string', length: 40)]
    public string $status;

    #[ORM\Column(name: 'archived_at', type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $archivedAt = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $updatedAt;

    /** @var Collection<int, CollectionEntryRecord> */
    #[ORM\OneToMany(targetEntity: CollectionEntryRecord::class, mappedBy: 'collection', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC', 'id' => 'ASC'])]
    public Collection $entries;

    /** @var Collection<int, CollectionPublicationHistoryRecord> */
    #[ORM\OneToMany(targetEntity: CollectionPublicationHistoryRecord::class, mappedBy: 'collection', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['publishedAt' => 'ASC', 'id' => 'ASC'])]
    public Collection $publications;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
        $this->publications = new ArrayCollection();
    }
}
