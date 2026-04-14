<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use App\Enum\SessionType;
use App\Enum\WeekStartDay;
use App\Repository\UserSettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserSettingsRepository::class)]
#[ORM\Table(name: 'user_settings')]
#[ORM\HasLifecycleCallbacks]
class UserSettings
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'userSettings')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Groups(['user:read'])]
    private User $user;

    #[ORM\Column(length: 64, options: ['default' => 'UTC'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $timezone = 'UTC';

    #[ORM\Column(enumType: WeekStartDay::class)]
    #[Groups(['user:read', 'user:write'])]
    private ?WeekStartDay $weekStartDay = WeekStartDay::MONDAY;

    #[ORM\Column(enumType: SessionType::class)]
    #[Groups(['user:read', 'user:write'])]
    private ?SessionType $defaultSessionType = SessionType::MANUAL;

    #[ORM\Column(type: Types::SMALLINT, options: ['unsigned' => true, 'default' => 50])]
    #[Assert\Positive]
    #[Groups(['user:read', 'user:write'])]
    private ?int $defaultSessionMinutes = 50;

    #[ORM\Column(type: Types::SMALLINT, options: ['unsigned' => true, 'default' => 10])]
    #[Assert\PositiveOrZero]
    #[Groups(['user:read', 'user:write'])]
    private ?int $defaultBreakMinutes = 10;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        if ($user->getUserSettings() !== $this){
            $user->setUserSettings($this);
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
