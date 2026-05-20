<?php

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    )
    {
        parent::__construct();
    }

    #[\Override]
    public static function class(): string
    {
        return User::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        $username = uniqid('user_', true);

        return [
            'username' => $username,
            'email' => "$username@studybuddy.test",
            'password' => 'secret',
            'displayName' => $username,
            'isActive' => true,
            'points' => 0,
            'settings' => SettingsFactory::new(),
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (User $user): void {
                $user->addRole('ROLE_USER');

                if (!str_starts_with((string) $user->getPassword(), '$')) {
                    $user->setPassword(
                        $this->userPasswordHasher->hashPassword(
                            $user,
                            (string) $user->getPassword()
                        )
                    );
                }
            });
    }

    /*
     * STATES
     */

    public function basic(int $number): static
    {
        return $this->with([
            'username' => "user{$number}",
            'email' => "user{$number}@studybuddy.test",
            'displayName' => "User {$number}",
            'lastLoginAt' => null,
        ]);
    }

    public function withPlainPassword(string $plainPassword): static
    {
        return $this->with([
            'password' => $plainPassword,
        ]);
    }

    public function isAdmin(): static
    {
        return $this->afterInstantiate(function (User $user): void {
            $user->addRole('ROLE_ADMIN');
        });
    }
}
