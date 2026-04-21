<?php

namespace App\Feature\Resource\Entity;

use App\Feature\Resource\Repository\ResourceTagRepository;
use App\Feature\Tag\Entity\Tag;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResourceTagRepository::class)]
#[ORM\Table(name: 'resource_tags')]
#[ORM\UniqueConstraint(name: 'UNIQ_RESOURCETAG_RESOURCE_TAG', columns: ['resource_id', 'tag_id'])]
#[ORM\Index(name: 'idx_resource_tags_resource_id', columns: ['resource_id'])]
#[ORM\Index(name: 'idx_resource_tags_tag_id', columns: ['tag_id'])]
#[ORM\HasLifecycleCallbacks]
class ResourceTag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Resource::class)]
    #[ORM\JoinColumn(name: 'resource_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Resource $resource = null;

    #[ORM\ManyToOne(targetEntity: Tag::class, inversedBy: 'resourceTags')]
    #[ORM\JoinColumn(name: 'tag_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Tag $tag = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt ??= new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResource(): ?Resource
    {
        return $this->resource;
    }

    public function setResource(?Resource $resource): static
    {
        $this->resource = $resource;

        return $this;
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(?Tag $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }
}
