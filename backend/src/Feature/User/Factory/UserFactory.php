<?php

namespace App\Feature\User\Factory;

use App\Feature\User\Entity\User;
use Override;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct(
        private readonly ?UserPasswordHasherInterface $userPasswordHasher = null,
    )
    {
        parent::__construct();
    }

    #[Override]
    public static function class(): string
    {
        return User::class;
    }

    public function admin(): static
    {
        return $this->with([
            'roles' => ['ROLE_ADMIN'],
            'username' => 'admin',
            'email' => 'admin@studybuddy.com',
            'displayName' => 'Admin',
        ]);
    }

    public function inactive(): static
    {
        return $this->with([
            'isActive' => false,
        ]);
    }

    /*
     *
     * FACTORY STATES
     *
     */

    public function withTimezone(string $timezone): static
    {
        return $this->afterInstantiate(function (User $user) use ($timezone) {
            $user->getSettings()?->setTimezone($timezone);
        });
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[Override]
    protected function defaults(): array|callable
    {
        return [
            'username' => self::faker()->unique()->userName(),

            // plain password; hashed in initialize()
            'password' => 'secret',

            'roles' => ['ROLE_USER'],
            'isActive' => true,

            // settings MUST exist (1:1 mapping)
            'settings' => SettingsFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[Override]
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (User $user): void {
                if (!$this->userPasswordHasher) {
                    return;
                }
                $user->setPassword(
                    $this->userPasswordHasher->hashPassword($user, (string)$user->getPassword())
                );
            });
    }
}
