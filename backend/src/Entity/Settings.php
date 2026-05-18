<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use App\Enum\WeekStartDay;
use App\LiveTimerSession\Enum\SessionType;
use App\Repository\SettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SettingsRepository::class)]
#[ORM\Table(name: 'settings')]
#[ORM\HasLifecycleCallbacks]
class Settings
{
    private const string DEFAULT_TIMEZONE = 'Europe/Brussels';
    private const int DEFAULT_SESSION_MINUTES = 50;
    private const int DEFAULT_BREAK_MINUTES = 10;

    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'settings')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Groups(['user:read'])]
    private User $user;

    #[ORM\Column(length: 64, options: ['default' => self::DEFAULT_TIMEZONE])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $timezone;

    #[ORM\Column(enumType: WeekStartDay::class)]
    #[Groups(['user:read', 'user:write'])]
    private ?WeekStartDay $weekStartDay;

    #[ORM\Column(enumType: SessionType::class)]
    #[Groups(['user:read', 'user:write'])]
    private ?SessionType $defaultSessionType;

    #[ORM\Column(type: Types::SMALLINT, options: ['unsigned' => true, 'default' => self::DEFAULT_SESSION_MINUTES])]
    #[Assert\Positive]
    #[Groups(['user:read', 'user:write'])]
    private ?int $defaultSessionMinutes;

    #[ORM\Column(type: Types::SMALLINT, options: ['unsigned' => true, 'default' => self::DEFAULT_BREAK_MINUTES])]
    #[Assert\PositiveOrZero]
    #[Groups(['user:read', 'user:write'])]
    private ?int $defaultBreakMinutes;

    public function __construct()
    {
        $this->timezone = self::DEFAULT_TIMEZONE;
        $this->weekStartDay = WeekStartDay::default();
        $this->defaultSessionType = SessionType::default();
        $this->defaultSessionMinutes = self::DEFAULT_SESSION_MINUTES;
        $this->defaultBreakMinutes = self::DEFAULT_BREAK_MINUTES;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        if ($user->getSettings() !== $this){
            $user->setSettings($this);
        }

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): static
    {
        $this->timezone = trim($timezone);

        return $this;
    }

    public function getWeekStartDay(): ?WeekStartDay
    {
        return $this->weekStartDay;
    }

    public function setWeekStartDay(WeekStartDay $weekStartDay): static
    {
        $this->weekStartDay = $weekStartDay;

        return $this;
    }

    public function getDefaultSessionType(): ?SessionType
    {
        return $this->defaultSessionType;
    }

    public function setDefaultSessionType(SessionType $defaultSessionType): static
    {
        $this->defaultSessionType = $defaultSessionType;

        return $this;
    }

    public function getDefaultSessionMinutes(): ?int
    {
        return $this->defaultSessionMinutes;
    }

    public function setDefaultSessionMinutes(int $defaultSessionMinutes): static
    {
        $this->defaultSessionMinutes = $defaultSessionMinutes;

        return $this;
    }

    public function getDefaultBreakMinutes(): ?int
    {
        return $this->defaultBreakMinutes;
    }

    public function setDefaultBreakMinutes(int $defaultBreakMinutes): static
    {
        $this->defaultBreakMinutes = $defaultBreakMinutes;

        return $this;
    }
}
