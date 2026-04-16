<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'tags')]
#[ORM\UniqueConstraint(name: 'UNIQ_TAG_USER_NAME', columns: ['user_id', 'name'])]
#[ORM\Index(name: 'idx_tags_user_id', columns: ['user_id'])]
#[ORM\HasLifecycleCallbacks]
class Tag
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tags')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 7, nullable: true)]
    #[Assert\Regex(pattern: '/^#[0-9A-Fa-f]{6}$/', message: 'Color must be a valid hex color.')]
    private ?string $colorHex = null;

    /**
     * @var Collection<int, NoteTag>
     */
    #[ORM\OneToMany(targetEntity: NoteTag::class, mappedBy: 'tag')]
    private Collection $noteTags;

    /**
     * @var Collection<int, TaskTag>
     */
    #[ORM\OneToMany(targetEntity: TaskTag::class, mappedBy: 'tag')]
    private Collection $taskTags;

    /**
     * @var Collection<int, SubjectTag>
     */
    #[ORM\OneToMany(targetEntity: SubjectTag::class, mappedBy: 'tag')]
    private Collection $subjectTags;

    /**
     * @var Collection<int, ResourceTag>
     */
    #[ORM\OneToMany(targetEntity: ResourceTag::class, mappedBy: 'tag')]
    private Collection $resourceTags;

    public function __construct()
    {
        $this->noteTags = new ArrayCollection();
        $this->taskTags = new ArrayCollection();
        $this->subjectTags = new ArrayCollection();
        $this->resourceTags = new ArrayCollection();
    }

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = trim($name);

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

    /**
     * @return Collection<int, NoteTag>
     */
    public function getNoteTags(): Collection
    {
        return $this->noteTags;
    }

    /**
     * @return Collection<int, TaskTag>
     */
    public function getTaskTags(): Collection
    {
        return $this->taskTags;
    }

    /**
     * @return Collection<int, SubjectTag>
     */
    public function getSubjectTags(): Collection
    {
        return $this->subjectTags;
    }

    /**
     * @return Collection<int, ResourceTag>
     */
    public function getResourceTags(): Collection
    {
        return $this->resourceTags;
    }
}
