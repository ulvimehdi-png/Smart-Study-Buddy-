<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Settings;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

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
        $user->setUsername('teststudent');
        $user->setPassword($this->hasher->hashPassword($user, 'password123'));

        $settings = new Settings();
        $settings->setUser($user);
        $user->setSettings($settings);

        $manager->persist($user);
        $manager->persist($settings);
        $manager->flush();
    }
}