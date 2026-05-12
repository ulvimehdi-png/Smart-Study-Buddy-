<?php

namespace App\Factory;

use App\Entity\Settings;
use App\Entity\User;
use App\Enum\WeekStartDay;
use App\LiveTimerSession\Enum\SessionType;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Settings>
 */
final class SettingsFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Settings::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            // No User default here - UserFactory owns automatic Settings creation
            'timezone' => 'Europe/Brussels',
            'weekStartDay' => WeekStartDay::default(),
            'defaultSessionType' => SessionType::default(),
            'defaultSessionMinutes' => 50,
            'defaultBreakMinutes' => 10,
            'createdAt' => new \DateTimeImmutable('-30 days'),
            'updatedAt' => new \DateTimeImmutable('-1 day'),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->with([
            'user' => $user,
        ]);
    }

    public function withTimezone(string $timezone): static
    {
        return $this->with([
            'timezone' => $timezone,
        ]);
    }

    public function withDefaults(int $sessionMinutes, int $breakMinutes): static
    {
        return $this->with([
            'defaultSessionMinutes' => $sessionMinutes,
            'defaultBreakMinutes' => $breakMinutes,
        ]);
    }
}
