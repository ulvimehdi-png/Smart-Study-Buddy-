<?php

namespace App\Service;

use App\Entity\Achievement;
use App\Entity\User;
use App\Entity\UserAchievement;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AchievementService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function addPoints(User $user, int $pointsToAdd): array
    {
        $user->addPoints($pointsToAdd);

        // 1. Get IDs of achievements the user already has
        $existing = $this->em->getRepository(UserAchievement::class)->findBy(['user' => $user]);
        $existingIds = array_map(fn($ua) => $ua->getAchievement()->getId(), $existing);

        // 2. Find all achievements in the database
        $allAchievements = $this->em->getRepository(Achievement::class)->findAll();

        $newlyUnlocked = [];

        // 3. Check if the new score unlocks anything new
        foreach ($allAchievements as $achievement) {
            if (!in_array($achievement->getId(), $existingIds) && $user->getPoints() >= $achievement->getPointsRequired()) {

                $ua = new UserAchievement();
                $ua->setUser($user);
                $ua->setAchievement($achievement);
                $this->em->persist($ua);

                $newlyUnlocked[] = $achievement;
            }
        }

        $this->em->flush();

        return $newlyUnlocked; // Returns unlocked badges (useful for popup animations later)
    }

    public function removePoints(User $user, int $pointsToRemove): void
    {
        // Deduct points, but don't let them go below 0
        $user->setPoints(max(0, $user->getPoints() - $pointsToRemove));
        $this->em->flush();

        // Note: We intentionally DO NOT take away badges once they are earned!
    }
}
