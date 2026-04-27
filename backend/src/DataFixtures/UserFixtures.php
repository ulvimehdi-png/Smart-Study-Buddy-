<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['minimal','base','dev','demo'];
    }

    public function getDependencies(): array
    {
        return [];
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUsername('admin');
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                'admin'
            )
        );
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $user->setIsActive(true);
        $user->setPoints(0);

        $manager->persist($user);
        $manager->flush();
    }
}
