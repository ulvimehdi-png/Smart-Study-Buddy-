<?php

namespace App\DataFixtures;

use App\Entity\Subject;
use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class SubjectTagFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            Subject::class,
            Tag::class,
        ];
    }

    /**
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['dev','demo'];
    }
}
