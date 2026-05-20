<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserAchievement;
use App\Service\AchievementService;
use App\Service\ProfileImageStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/achievements')]
final class AchievementController extends AbstractController
{
    #[Route('/leaderboard', methods: ['GET'])]
    public function getLeaderboard(
        EntityManagerInterface $em,
        ProfileImageStorage $profileImageStorage
    ): JsonResponse {
        $users = $em->getRepository(User::class)
            ->createQueryBuilder('u')
            ->orderBy('u.points', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $leaderboard = array_map(
            static fn (User $u): array => [
                'username' => $u->getUsername(),
                'points' => $u->getPoints(),
                'profileImagePath' => $profileImageStorage->publicUrl($u->getProfileImagePath()),
            ],
            $users
        );

        return $this->json($leaderboard);
    }

    #[Route('/mine', methods: ['GET'])]
    public function getMyAchievements(#[CurrentUser] ?User $user, EntityManagerInterface $em): JsonResponse
    {
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $userAchievements = $em->getRepository(UserAchievement::class)->findBy(['user' => $user]);

        $badges = array_map(
            static fn (UserAchievement $ua): array => [
                'name' => $ua->getAchievement()->getName(),
                'description' => $ua->getAchievement()->getDescription(),
                'earnedAt' => $ua->getEarnedAt()->format(\DATE_ATOM),
            ],
            $userAchievements
        );

        return $this->json([
            'points' => $user->getPoints(),
            'badges' => $badges,
        ]);
    }

    #[Route('/award-manual', methods: ['POST'])]
    public function awardManual(
        Request $request,
        #[CurrentUser] ?User $user,
        AchievementService $achievementService
    ): JsonResponse {
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $data = $request->toArray();
        $minutes = (int) ($data['minutes'] ?? 0);

        if ($minutes <= 0) {
            return $this->json(['error' => 'Invalid duration.'], 400);
        }

        $achievementService->addPoints($user, $minutes);

        return $this->json([
            'success' => true,
            'minutes_logged' => $minutes
        ]);
    }
}
