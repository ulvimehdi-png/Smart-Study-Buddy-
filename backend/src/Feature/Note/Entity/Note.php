<?php

namespace App\Feature\Note\Entity;


use App\Feature\LiveTimerSession\Entity\SessionEntry;
use App\Feature\Note\Enum\NoteSourceType;
use App\Feature\Note\Repository\NoteRepository;
use App\Feature\Subject\Entity\Subject;
use App\Feature\Task\Entity\Task;
use App\Feature\User\Entity\User;
use App\General\Entity\Traits\ArchivableSoftDeletableTrait;
use App\General\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: NoteRepository::class)]
#[ORM\Table(name: 'notes')]
#[ORM\Index(name: 'idx_notes_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_notes_subject_id', columns: ['subject_id'])]
#[ORM\Index(name: 'idx_notes_task_id', columns: ['task_id'])]
#[ORM\Index(name: 'idx_notes_source_type', columns: ['source_type'])]
#[ORM\Index(name: 'idx_notes_archived_at', columns: ['archived_at'])]
#[ORM\Index(name: 'idx_notes_deleted_at', columns: ['deleted_at'])]
#[ORM\HasLifecycleCallbacks]
class Note
{
    use TimestampableTrait;
    use ArchivableSoftDeletableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read', 'note:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'notes')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Subject::class, inversedBy: 'notes')]
    #[ORM\JoinColumn(name: 'subject_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[Groups(['note:read'])]
    private ?Subject $subject = null;

    #[ORM\ManyToOne(targetEntity: Task::class, inversedBy: 'notes')]
    #[ORM\JoinColumn(name: 'task_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[Groups(['note:read'])]
    private ?Task $task = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    #[Groups(['read', 'note:read', 'note:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['read', 'note:read', 'note:write'])]
    private ?string $content = null;

    #[ORM\Column(name: 'source_type', enumType: NoteSourceType::class)]
    #[Assert\NotNull]
    #[Groups(['read', 'note:read', 'note:write'])]
    private ?NoteSourceType $sourceType = null;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Assert\Length(max: 1024)]
    #[Assert\Url]
    #[Groups(['read', 'note:read', 'note:write'])]
    private ?string $externalUrl = null;

    /**
     * @var Collection<int, NoteAttachment>
     */
    #[ORM\OneToMany(targetEntity: NoteAttachment::class, mappedBy: 'note', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $noteAttachments;

    /**
     * @var Collection<int, NoteTag>
     */
    #[ORM\OneToMany(targetEntity: NoteTag::class, mappedBy: 'note')]
    private Collection $noteTags;

    /**
     * @var Collection<int, SessionEntry>
     */
    #[ORM\OneToMany(targetEntity: SessionEntry::class, mappedBy: 'note')]
    private Collection $sessionEntries;

    public function __construct()
    {
        $this->noteAttachments = new ArrayCollection();
        $this->noteTags = new ArrayCollection();
        $this->sessionEntries = new ArrayCollection();
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

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setSubject(?Subject $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): static
    {
        $this->task = $task;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getSourceType(): ?NoteSourceType
    {
        return $this->sourceType;
    }

    public function setSourceType(NoteSourceType $sourceType): static
    {
        $this->sourceType = $sourceType;

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

    #[Assert\Callback]
    public function validateExternalUrl(ExecutionContextInterface $context): void
    {
        if (
            null !== $this->sourceType
            && $this->sourceType->value === 'link'
            && (null === $this->externalUrl || '' === trim($this->externalUrl))
        ) {
            $context
                ->buildViolation('externalUrl is required when sourceType is link.')
                ->atPath('externalUrl')
                ->addViolation();
        }
    }

    /**
     * @return Collection<int, NoteAttachment>
     */
    public function getNoteAttachments(): Collection
    {
        return $this->noteAttachments;
    }

    public function addNoteAttachment(NoteAttachment $noteAttachment): static
    {
        if (!$this->noteAttachments->contains($noteAttachment)) {
            $this->noteAttachments->add($noteAttachment);
            $noteAttachment->setNote($this);
        }

        return $this;
    }

    public function removeNoteAttachment(NoteAttachment $noteAttachment): static
    {
        if ($this->noteAttachments->removeElement($noteAttachment)) {
            // set the owning side to null (unless already changed)
            if ($noteAttachment->getNote() === $this) {
                $noteAttachment->setNote(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, NoteTag>
     */
    public function getNoteTags(): Collection
    {
        return $this->noteTags;
    }

    public function addNoteTag(NoteTag $noteTag): static
    {
        if (!$this->noteTags->contains($noteTag)) {
            $this->noteTags->add($noteTag);
            $noteTag->setNote($this);
        }

        return $this;
    }

    public function removeNoteTag(NoteTag $noteTag): static
    {
        if ($this->noteTags->removeElement($noteTag)) {
            if ($noteTag->getNote() === $this) {
                $noteTag->setNote(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SessionEntry>
     */
    public function getSessionEntries(): Collection
    {
        return $this->sessionEntries;
    }

    public function addSessionEntry(SessionEntry $sessionEntry): static
    {
        if (!$this->sessionEntries->contains($sessionEntry)) {
            $this->sessionEntries->add($sessionEntry);
            $sessionEntry->setNote($this);
        }

        return $this;
    }

    public function removeSessionEntry(SessionEntry $sessionEntry): static
    {
        if ($this->sessionEntries->removeElement($sessionEntry)) {
            // set the owning side to null (unless already changed)
            if ($sessionEntry->getNote() === $this) {
                $sessionEntry->setNote(null);
            }
        }

        return $this;
    }
}
