<?php

namespace App\DataFixtures;

use App\Entity\Achievement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AchievementFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $badges = [
            ['Starter', 'Earned your first 50 points.', 50],
            ['Scholar', 'Reached 200 points.', 200],
            ['Dedicated Student', 'Accumulated 500 points.', 500],
            ['Master of Focus', 'A true master with 1000 points.', 1000],
            ['Academic Legend', 'Unstoppable! Reached 2000 points.', 2000],
        ];

        foreach ($badges as [$name, $description, $points]) {
            $achievement = new Achievement();
            $achievement->setName($name);
            $achievement->setDescription($description);
            $achievement->setPointsRequired($points);
            $manager->persist($achievement);
        }

        $manager->flush();
    }
}