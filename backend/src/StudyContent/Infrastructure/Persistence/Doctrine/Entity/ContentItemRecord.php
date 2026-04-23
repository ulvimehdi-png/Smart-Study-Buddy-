<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'study_content_items')]
final class ContentItemRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(name: 'owner_id', type: 'string', length: 36)]
    public string $ownerId;

    #[ORM\Column(type: 'string', length: 50)]
    public string $kind;

    #[ORM\Column(type: 'string', length: 200)]
    public string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $body = null;

    #[ORM\Column(type: 'string', length: 40)]
    public string $scope;

    #[ORM\Column(name: 'lifecycle_status', type: 'string', length: 40)]
    public string $lifecycleStatus;

    #[ORM\Column(name: 'canonical_status', type: 'string', length: 40)]
    public string $canonicalStatus;

    #[ORM\Column(name: 'publication_policy_requires_approval', type: 'boolean')]
    public bool $publicationPolicyRequiresApproval;

    #[ORM\Column(name: 'publication_policy_allow_personal', type: 'boolean')]
    public bool $publicationPolicyAllowPersonal;

    #[ORM\Column(name: 'publication_policy_allow_shared_group', type: 'boolean')]
    public bool $publicationPolicyAllowSharedGroup;

    #[ORM\Column(name: 'publication_policy_allow_institution_managed', type: 'boolean')]
    public bool $publicationPolicyAllowInstitutionManaged;

    #[ORM\Column(name: 'revision_number', type: 'integer')]
    public int $revisionNumber;

    #[ORM\Column(name: 'superseded_by_content_item_id', type: 'string', length: 36, nullable: true)]
    public ?string $supersededByContentItemId = null;

    #[ORM\Column(name: 'approved_at', type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $approvedAt = null;

    #[ORM\Column(name: 'blocked_at', type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $blockedAt = null;

    #[ORM\Column(name: 'archived_at', type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $archivedAt = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'superseded_at', type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $supersededAt = null;

    /** @var Collection<int, ContentProvenanceSourceRecord> */
    #[ORM\OneToMany(targetEntity: ContentProvenanceSourceRecord::class, mappedBy: 'contentItem', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['recordedAt' => 'ASC', 'id' => 'ASC'])]
    public Collection $provenanceSources;

    /** @var Collection<int, ContentRelationshipRecord> */
    #[ORM\OneToMany(targetEntity: ContentRelationshipRecord::class, mappedBy: 'contentItem', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['recordedAt' => 'ASC', 'id' => 'ASC'])]
    public Collection $relationships;

    /** @var Collection<int, ContentReviewRecord> */
    #[ORM\OneToMany(targetEntity: ContentReviewRecord::class, mappedBy: 'contentItem', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['reviewedAt' => 'ASC', 'id' => 'ASC'])]
    public Collection $reviews;

    /** @var Collection<int, ContentPublicationHistoryRecord> */
    #[ORM\OneToMany(targetEntity: ContentPublicationHistoryRecord::class, mappedBy: 'contentItem', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['publishedAt' => 'ASC', 'id' => 'ASC'])]
    public Collection $publications;

    public function __construct()
    {
        $this->provenanceSources = new ArrayCollection();
        $this->relationships = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->publications = new ArrayCollection();
    }
}
