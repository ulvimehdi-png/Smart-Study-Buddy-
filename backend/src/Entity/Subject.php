<?php

namespace App\Entity;

use App\Entity\Traits\ArchivableSoftDeletableTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Repository\SubjectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SubjectRepository::class)]
#[ORM\Table(name: 'subjects')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USER_CODE', columns: ['user_id', 'code'])]
#[ORM\Index(name: 'idx_subjects_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_subjects_archived_at', columns: ['archived_at'])]
#[ORM\Index(name: 'idx_subjects_deleted_at', columns: ['deleted_at'])]
#[ORM\HasLifecycleCallbacks]
class Subject
{
    use TimestampableTrait;
    use ArchivableSoftDeletableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read', 'subject:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class ,inversedBy: 'subjects')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 32, nullable: true)]
    #[Assert\Length(max: 32)]
    #[Groups(['read', 'subject:read', 'subject:write'])]
    private ?string $code = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    #[Groups(['read', 'subject:read', 'subject:write'])]
    private ?string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['read', 'subject:read', 'subject:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 7, nullable: true)]
    #[Assert\Regex(pattern: '/^#[0-9A-Fa-f]{6}$/', message: 'Color must be a valid hex color.')]
    #[Groups(['read', 'subject:read', 'subject:write'])]
    private ?string $colorHex = null;

    #[ORM\Column(nullable: true, options: ['unsigned' => true])]
    #[Assert\PositiveOrZero]
    #[Groups(['read', 'subject:read', 'subject:write'])]
    private ?int $targetTotalMinutes = null;

    #[ORM\Column(nullable: true, options: ['unsigned' => true])]
    #[Assert\PositiveOrZero]
    #[Groups(['read', 'subject:read', 'subject:write'])]
    private ?int $targetWeeklyMinutes = null;

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = null !== $code ? trim($code) : null;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = trim($name);

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

    public function getColorHex(): ?string
    {
        return $this->colorHex;
    }

    public function setColorHex(?string $colorHex): static
    {
        $this->colorHex = null !== $colorHex ? strtoupper(trim($colorHex)) : null;

        return $this;
    }

    public function getTargetTotalMinutes(): ?int
    {
        return $this->targetTotalMinutes;
    }

    public function setTargetTotalMinutes(?int $targetTotalMinutes): static
    {
        $this->targetTotalMinutes = $targetTotalMinutes;

        return $this;
    }

    public function getTargetWeeklyMinutes(): ?int
    {
        return $this->targetWeeklyMinutes;
    }

    public function setTargetWeeklyMinutes(?int $targetWeeklyMinutes): static
    {
        $this->targetWeeklyMinutes = $targetWeeklyMinutes;

        return $this;
    }
}
