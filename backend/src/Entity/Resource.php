<?php

namespace App\Entity;

use App\Entity\Traits\ArchivableSoftDeletableTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Enum\ResourceType;
use App\Repository\ResourceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ResourceRepository::class)]
#[ORM\Table(name: 'resources')]
#[ORM\Index(name: 'idx_resources_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_resources_subject_id', columns: ['subject_id'])]
#[ORM\Index(name: 'idx_resources_resource_type', columns: ['resource_type'])]
#[ORM\Index(name: 'idx_resources_archived_at', columns: ['archived_at'])]
#[ORM\Index(name: 'idx_resources_deleted_at', columns: ['deleted_at'])]
#[ORM\HasLifecycleCallbacks]
class Resource
{
    use TimestampableTrait;
    use ArchivableSoftDeletableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'resources')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Subject::class, inversedBy: 'resources')]
    #[ORM\JoinColumn(name: 'subject_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Subject $subject = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'resource_type', enumType: ResourceType::class)]
    #[Assert\NotNull]
    private ?ResourceType $resourceType = null;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Assert\Length(max: 1024)]
    #[Assert\Url]
    private ?string $externalUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $originalFilename = null;

    #[ORM\Column(length: 512, nullable: true)]
    #[Assert\Length(max: 512)]
    private ?string $storagePath = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $mimeType = null;

    #[ORM\Column(nullable: true, options: ['unsigned' => true])]
    #[Assert\PositiveOrZero]
    private ?int $sizeBytes = null;

    #[ORM\Column(length: 64, nullable: true)]
    #[Assert\Length(min: 64, max: 64)]
    private ?string $checksumSha256 = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setSubject(?Subject $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = trim($title);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getResourceType(): ?ResourceType
    {
        return $this->resourceType;
    }

    public function setResourceType(ResourceType $resourceType): static
    {
        $this->resourceType = $resourceType;

        return $this;
    }

    public function getExternalUrl(): ?string
    {
        return $this->externalUrl;
    }

    public function setExternalUrl(?string $externalUrl): static
    {
        $this->externalUrl = null !== $externalUrl ? trim($externalUrl) : null;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(?string $originalFilename): static
    {
        $this->originalFilename = null !== $originalFilename ? trim($originalFilename) : null;

        return $this;
    }

    public function getStoragePath(): ?string
    {
        return $this->storagePath;
    }

    public function setStoragePath(?string $storagePath): static
    {
        $this->storagePath = null !== $storagePath ? trim($storagePath) : null;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): static
    {
        $this->mimeType = null !== $mimeType ? trim($mimeType) : null;

        return $this;
    }

    public function getSizeBytes(): ?int
    {
        return $this->sizeBytes;
    }

    public function setSizeBytes(?int $sizeBytes): static
    {
        $this->sizeBytes = $sizeBytes;

        return $this;
    }

    public function getChecksumSha256(): ?string
    {
        return $this->checksumSha256;
    }

    public function setChecksumSha256(?string $checksumSha256): static
    {
        $this->checksumSha256 = null !== $checksumSha256 ? strtolower(trim($checksumSha256)) : null;

        return $this;
    }

    #[Assert\Callback]
    public function validateResourceFields(ExecutionContextInterface $context): void
    {
        if (
            null !== $this->resourceType
            && $this->resourceType->value === 'link'
            && (null === $this->externalUrl || '' === trim($this->externalUrl))
        ) {
            $context
                ->buildViolation('externalUrl is required when resourceType is link.')
                ->atPath('externalUrl')
                ->addViolation();
        }

        if (
            null !== $this->resourceType
            && $this->resourceType->value === 'file'
            && (null === $this->storagePath || '' === trim($this->storagePath))
        ) {
            $context
                ->buildViolation('storagePath is required when resourceType is file.')
                ->atPath('storagePath')
                ->addViolation();
        }
    }
}
