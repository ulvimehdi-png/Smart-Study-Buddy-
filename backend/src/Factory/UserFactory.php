<?php

namespace App\Factory;

use App\Entity\User;
use App\Entity\Settings;
use App\Enum\WeekStartDay;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFactory
{
    public function __construct(
        private UserPasswordHasherInterface $hasher
    ){}

    /*
     * Creates a fully valid User aggregate root (--> incl Settings)
     */
    public function create(
        string $username,
        string $plainPassword,
        ?string $email = null,
        ?string $displayName = null,
        array $roles = ['ROLE_USER'],

    ): User
    {
        $user = new User();

        // Core identity
        $user->setUsername($username);
        $user->
    }

    /*
     * Creates default settings tied to a user
     */
    private function createDefaultSettings(User $user, string $timezone): Settings
    {
        $settings = new Settings();

        $settings->setUser($user);
        $settings->setTimezone($timezone);

        // Standard defaults
        $settings->setWeekStartDay(WeekStartDay::MONDAY);
        $settings->
    }
}
