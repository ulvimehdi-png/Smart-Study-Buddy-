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
     * Creates a fully valid User with default Settings
     */
    public function create(
        string  $username,
        string  $plainPassword,
        ?string $email = null,
        ?string $displayName = null,
        ?array  $roles = null,

    ): User
    {
        $user = new User();

        $user->setUsername($username);
        $user->setEmail($email);
        $user->setDisplayName($displayName);
        if ($roles !== null) $user->setRoles($roles);

        $user->setPassword(
            $this->hasher->hashPassword($user, $plainPassword)
        );

        $settings = new Settings();
        $user->setSettings($settings);

        return $user;
    }
}
