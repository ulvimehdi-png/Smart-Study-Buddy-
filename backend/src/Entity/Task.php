<?php

namespace App\Entity;

use App\Entity\Traits\ArchivableSoftDeletableTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Enum\TaskPriority;
use App\Enum\TaskStatus;
use App\Repository\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(name: 'tasks')]
#[ORM\Index(name: 'idx_tasks_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_tasks_subject_id', columns: ['subject_id'])]
#[ORM\Index(name: 'idx_tasks_status', columns: ['status'])]
#[ORM\Index(name: 'idx_tasks_priority', columns: ['priority'])]
#[ORM\Index(name: 'idx_tasks_due_at', columns: ['due_at'])]
#[ORM\Index(name: 'idx_tasks_scheduled_for', columns: ['scheduled_for'])]
#[ORM\Index(name: 'idx_tasks_archived_at', columns: ['archived_at'])]
#[ORM\Index(name: 'idx_tasks_deleted_at', columns: ['deleted_at'])]
#[ORM\HasLifecycleCallbacks]
class Task
{
    use TimestampableTrait;
    use ArchivableSoftDeletableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read', 'task:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Subject::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(name: 'subject_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[Groups(['task:read'])]
    private ?Subject $subject = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['read', 'task:read', 'task:write'])]
    private string $title;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['read', 'task:read', 'task:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 20, enumType: TaskStatus::class, options: ['default' => TaskStatus::TODO])]
    #[Groups(['read', 'task:read', 'task:write'])]
    private ?TaskStatus $status = null;

    #[ORM\Column(length: 20, enumType: TaskPriority::class, options: ['default' => TaskPriority::MEDIUM])]
    #[Groups(['read', 'task:read', 'task:write'])]
    private ?TaskPriority $priority = null;

    #[ORM\Column(nullable: true, options: ['unsigned' => true])]
    #[Assert\PositiveOrZero]
    #[Groups(['read', 'task:read', 'task:write'])]
    private ?int $estimatedMinutes = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['read', 'task:read', 'task:write'])]
    private ?\DateTimeImmutable $dueAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['read', 'task:read', 'task:write'])]
    private ?\DateTimeImmutable $scheduledFor = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['read', 'task:read'])]
    private ?\DateTimeImmutable $completedAt = null;

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

    public function getStatus(): ?TaskStatus
    {
        return $this->status;
    }

    public function setStatus(TaskStatus $status): static
    {
        $this->status = $status;

        if ($status !== TaskStatus::DONE) {
            $this->completedAt = null;
        }

        return $this;
    }

    public function markDone(?\DateTimeImmutable $completedAt = null): static
    {
        $this->status = TaskStatus::DONE;
        $this->completedAt = $completedAt ?? new \DateTimeImmutable();

        return $this;
    }

    public function getPriority(): ?TaskPriority
    {
        return $this->priority;
    }

    public function setPriority(TaskPriority $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getEstimatedMinutes(): ?int
    {
        return $this->estimatedMinutes;
    }

    public function setEstimatedMinutes(?int $estimatedMinutes): static
    {
        $this->estimatedMinutes = $estimatedMinutes;

        return $this;
    }

    public function getDueAt(): ?\DateTimeImmutable
    {
        return $this->dueAt;
    }

    public function setDueAt(?\DateTimeImmutable $dueAt): static
    {
        $this->dueAt = $dueAt;

        return $this;
    }

    public function getScheduledFor(): ?\DateTimeImmutable
    {
        return $this->scheduledFor;
    }

    public function setScheduledFor(?\DateTimeImmutable $scheduledFor): static
    {
        $this->scheduledFor = $scheduledFor;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;

        return $this;
    }
}
