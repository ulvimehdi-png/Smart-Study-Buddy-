<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use App\Repository\SessionEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: SessionEntryRepository::class)]
#[ORM\Table(name: 'session_entries')]
#[ORM\Index(name: 'idx_session_entries_session_id', columns: ['session_id'])]
#[ORM\Index(name: 'idx_session_entries_subject_id', columns: ['subject_id'])]
#[ORM\Index(name: 'idx_session_entries_task_id', columns: ['task_id'])]
#[ORM\Index(name: 'idx_session_entries_note_id', columns: ['note_id'])]
#[ORM\HasLifecycleCallbacks]
class SessionEntry
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Session::class, inversedBy: 'sessionEntries')]
    #[ORM\JoinColumn(name: 'session_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Session $session = null;

    #[ORM\ManyToOne(targetEntity: Subject::class, inversedBy: 'sessionEntries')]
    #[ORM\JoinColumn(name: 'subject_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Subject $subject = null;

    #[ORM\ManyToOne(targetEntity: Task::class, inversedBy: 'sessionEntries')]
    #[ORM\JoinColumn(name: 'task_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Task $task = null;

    #[ORM\ManyToOne(targetEntity: Note::class, inversedBy: 'sessionEntries')]
    #[ORM\JoinColumn(name: 'note_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Note $note = null;

    #[ORM\Column(name: 'planned_minutes', nullable: true, options: ['unsigned' => true])]
    #[Assert\PositiveOrZero]
    private ?int $plannedMinutes = null;

    #[ORM\Column(name: 'actual_focus_minutes', nullable: true, options: ['unsigned' => true])]
    #[Assert\PositiveOrZero]
    private ?int $actualFocusMinutes = null;

    #[ORM\Column(name: 'entry_started_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $entryStartedAt = null;

    #[ORM\Column(name: 'entry_ended_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $entryEndedAt = null;

    #[ORM\Column(name: 'subject_name_snapshot', length: 120, nullable: true)]
    private ?string $subjectNameSnapshot = null;

    #[ORM\Column(name: 'task_title_snapshot', length: 255, nullable: true)]
    private ?string $taskTitleSnapshot = null;

    #[ORM\Column(name: 'note_title_snapshot', length: 150, nullable: true)]
    private ?string $noteTitleSnapshot = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): static
    {
        $this->session = $session;

        return $this;
    }

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setSubject(?Subject $subject): static
    {
        $this->subject = $subject;
        if (null !== $subject && null === $this->subjectNameSnapshot) {
            $this->subjectNameSnapshot = $subject->getName();
        }
        return $this;
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): static
    {
        $this->task = $task;
        if (null !== $task && null === $this->taskTitleSnapshot) {
            $this->taskTitleSnapshot = $task->getTitle();
        }
        return $this;
    }

    public function getNote(): ?Note
    {
        return $this->note;
    }

    public function setNote(?Note $note): static
    {
        $this->note = $note;
        if (null !== $note && null === $this->noteTitleSnapshot) {
            $this->noteTitleSnapshot = $note->getTitle();
        }
        return $this;
    }

    public function getPlannedMinutes(): ?int
    {
        return $this->plannedMinutes;
    }

    public function setPlannedMinutes(?int $plannedMinutes): static
    {
        $this->plannedMinutes = $plannedMinutes;

        return $this;
    }

    public function getActualFocusMinutes(): ?int
    {
        return $this->actualFocusMinutes;
    }

    public function setActualFocusMinutes(?int $actualFocusMinutes): static
    {
        $this->actualFocusMinutes = $actualFocusMinutes;

        return $this;
    }

    public function getEntryStartedAt(): ?\DateTimeImmutable
    {
        return $this->entryStartedAt;
    }

    public function setEntryStartedAt(?\DateTimeImmutable $entryStartedAt): static
    {
        $this->entryStartedAt = $entryStartedAt;

        return $this;
    }

    public function getEntryEndedAt(): ?\DateTimeImmutable
    {
        return $this->entryEndedAt;
    }

    public function setEntryEndedAt(?\DateTimeImmutable $entryEndedAt): static
    {
        $this->entryEndedAt = $entryEndedAt;

        return $this;
    }

    public function getSubjectNameSnapshot(): ?string
    {
        return $this->subjectNameSnapshot;
    }

    public function setSubjectNameSnapshot(?string $subjectNameSnapshot): static
    {
        $this->subjectNameSnapshot = $subjectNameSnapshot;

        return $this;
    }

    public function getTaskTitleSnapshot(): ?string
    {
        return $this->taskTitleSnapshot;
    }

    public function setTaskTitleSnapshot(?string $taskTitleSnapshot): static
    {
        $this->taskTitleSnapshot = $taskTitleSnapshot;

        return $this;
    }

    public function getNoteTitleSnapshot(): ?string
    {
        return $this->noteTitleSnapshot;
    }

    public function setNoteTitleSnapshot(?string $noteTitleSnapshot): static
    {
        $this->noteTitleSnapshot = $noteTitleSnapshot;

        return $this;
    }

    #[Assert\Callback]
    public function validateChronology(ExecutionContextInterface $context): void
    {
        if (
            null !== $this->entryStartedAt
            && null !== $this->entryEndedAt
            && $this->entryEndedAt < $this->entryStartedAt
        ) {
            $context
                ->buildViolation('entryEndedAt cannot be earlier than entryStartedAt.')
                ->atPath('entryEndedAt')
                ->addViolation();
        }
    }
}
