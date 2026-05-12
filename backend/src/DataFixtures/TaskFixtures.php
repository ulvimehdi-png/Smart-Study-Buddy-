<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskPriority;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class TaskFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $user = $manager->getRepository(User::class)->findOneBy(['username' => 'admin']);
        if (!$user) return;

        $task1 = new Task();
        $task1->setUser($user);
        $task1->setTitle('Finish Web Project');
        $task1->setPriority(TaskPriority::HIGH);
        $manager->persist($task1);

        $task2 = new Task();
        $task2->setUser($user);
        $task2->setTitle('Buy Milk');
        $task2->setPriority(TaskPriority::LOW);
        $manager->persist($task2);

        $task3 = new Task();
        $task3->setUser($user);
        $task3->setTitle('Study for Exams');
        $task3->setPriority(TaskPriority::MEDIUM);
        $manager->persist($task3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            SubjectFixtures::class
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
