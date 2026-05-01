<?php

namespace App\Tests\Service;

use App\Entity\Achievement;
use App\Entity\User;
use App\Service\AchievementService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AchievementServiceTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private AchievementService $achievementService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->achievementService = static::getContainer()->get(AchievementService::class);
    }

    public function testAddPointsUnlocksAchievement(): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\UserAchievement')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Achievement')->execute();

        $user = new User();
        $user->setUsername('achiever_' . uniqid());
        $user->setPassword('secret');
        $user->setPoints(0);
        $this->entityManager->persist($user);

        $achievement = new Achievement();
        $achievement->setName('First 50');
        $achievement->setDescription('Get 50 points');
        $achievement->setPointsRequired(50);
        $this->entityManager->persist($achievement);

        $this->entityManager->flush();

        $newlyUnlocked = $this->achievementService->addPoints($user, 50);

        $this->assertCount(1, $newlyUnlocked);
        $this->assertSame('First 50', $newlyUnlocked[0]->getName());

        $newlyUnlockedAgain = $this->achievementService->addPoints($user, 10);
        $this->assertCount(0, $newlyUnlockedAgain);
        $this->assertSame(60, $user->getPoints());
    }

    public function testRemovePointsDoesNotGoBelowZero(): void
    {
        $user = new User();
        $user->setUsername('loser_' . uniqid());
        $user->setPassword('secret');
        $user->setPoints(10);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->achievementService->removePoints($user, 15);

        $this->assertSame(0, $user->getPoints());
    }
}