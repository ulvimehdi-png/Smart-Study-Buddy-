<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use App\Enum\SessionStatus;
use App\Enum\SessionType;
use App\Repository\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[ORM\Table(name: 'sessions')]
#[ORM\Index(name: 'idx_sessions_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_sessions_status', columns: ['status'])]
#[ORM\Index(name: 'idx_sessions_session_type', columns: ['session_type'])]
#[ORM\Index(name: 'idx_sessions_started_at', columns: ['started_at'])]
#[ORM\HasLifecycleCallbacks]
class Session
{
    private const int DEFAULT_BREAK_MINUTES = 0;
    private const int DEFAULT_DISTRACTION_COUNT = 0;

    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read', 'session:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sessions')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(enumType: SessionStatus::class)]
    #[Assert\NotNull]
    #[Groups(['read', 'session:read', 'session:write'])]
    private ?SessionStatus $status = null;

    #[ORM\Column(enumType: SessionType::class)]
    #[Assert\NotNull]
    #[Groups(['read', 'session:read', 'session:write'])]
    private ?SessionType $sessionType = null;

    #[ORM\Column(name: 'started_at', type: Types::DATETIME_IMMUTABLE)]
    #[Assert\NotNull]
    #[Groups(['read', 'session:read', 'session:write'])]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(name: 'ended_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['read', 'session:read', 'session:write'])]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column(name: 'planned_minutes', nullable: true, options: ['unsigned' => true])]
    #[Assert\PositiveOrZero]
    #[Groups(['read', 'session:read', 'session:write'])]
    private ?int $plannedMinutes = null;

    #[ORM\Column(name: 'actual_focus_minutes', nullable: true, options: ['unsigned' => true])]
    #[Assert\PositiveOrZero]
    #[Groups(['read', 'session:read', 'session:write'])]
    private ?int $actualFocusMinutes = null;

    #[ORM\Column(name: 'break_minutes', options: ['unsigned' => true, 'default' => self::DEFAULT_BREAK_MINUTES])]
    #[Assert\PositiveOrZero]
    #[Groups(['read', 'session:read', 'session:write'])]
    private ?int $breakMinutes;

    #[ORM\Column(name: 'distraction_count', options: ['unsigned' => true, 'default' => self::DEFAULT_DISTRACTION_COUNT])]
    #[Assert\PositiveOrZero]
    #[Groups(['read', 'session:read', 'session:write'])]
    private ?int $distractionCount;

    #[ORM\Column(name: 'session_note', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['read', 'session:read', 'session:write'])]
    private ?string $sessionNote = null;

    /**
     * @var Collection<int, SessionEntry>
     */
    #[ORM\OneToMany(targetEntity: SessionEntry::class, mappedBy: 'session', cascade: ['persist', 'remove'])]
    private Collection $sessionEntries;

    public function __construct()
    {
        $this->breakMinutes = self::DEFAULT_BREAK_MINUTES;
        $this->distractionCount = self::DEFAULT_DISTRACTION_COUNT;

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

    public function getStatus(): ?SessionStatus
    {
        return $this->status;
    }

    public function setStatus(SessionStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getSessionType(): ?SessionType
    {
        return $this->sessionType;
    }

    public function setSessionType(SessionType $sessionType): static
    {
        $this->sessionType = $sessionType;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): static
    {
        $this->endedAt = $endedAt;

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

    public function getBreakMinutes(): ?int
    {
        return $this->breakMinutes;
    }

    public function setBreakMinutes(int $breakMinutes): static
    {
        $this->breakMinutes = $breakMinutes;

        return $this;
    }

    public function getDistractionCount(): ?int
    {
        return $this->distractionCount;
    }

    public function setDistractionCount(int $distractionCount): static
    {
        $this->distractionCount = $distractionCount;

        return $this;
    }

    public function getSessionNote(): ?string
    {
        return $this->sessionNote;
    }

    public function setSessionNote(?string $sessionNote): static
    {
        $this->sessionNote = null !== $sessionNote ? trim($sessionNote) : null;

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
            $sessionEntry->setSession($this);
        }

        return $this;
    }

    public function removeSessionEntry(SessionEntry $sessionEntry): static
    {
        if ($this->sessionEntries->removeElement($sessionEntry)) {
            // set the owning side to null (unless already changed)
            if ($sessionEntry->getSession() === $this) {
                $sessionEntry->setSession(null);
            }
        }

        return $this;
    }

    #[Assert\Callback]
    public function validateChronology(ExecutionContextInterface $context): void
    {
        if (
            null !== $this->startedAt
            && null !== $this->endedAt
            && $this->endedAt < $this->startedAt
        ) {
            $context
                ->buildViolation('endedAt cannot be earlier than startedAt.')
                ->atPath('endedAt')
                ->addViolation();
        }

        if (
            null !== $this->status
            && $this->status->value === 'completed'
            && null === $this->endedAt
        ) {
            $context
                ->buildViolation('A completed session must have endedAt set.')
                ->atPath('endedAt')
                ->addViolation();
        }
    }
}
