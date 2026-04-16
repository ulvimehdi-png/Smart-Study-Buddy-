<?php

namespace App\DataFixtures;

use App\Entity\Note;
use App\Entity\Session;
use App\Entity\Subject;
use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class SessionEntryFixtures extends Fixture implements FixtureGroupInterface
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
            Session::class,
            Subject::class,
            Task::class,
            Note::class,
        ];
    }

    /**
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['dev','demo','ui','large'];
    }
}
