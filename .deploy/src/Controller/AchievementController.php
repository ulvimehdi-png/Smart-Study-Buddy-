<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserAchievement;
use App\Service\ProfileImageStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        // Get the top 10 students by points
        $users = $em->getRepository(User::class)
            ->createQueryBuilder('u')
            ->orderBy('u.points', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $leaderboard = [];
        foreach ($users as $u) {
            $leaderboard[] = [
                'username' => $u->getUsername(),
                'points' => $u->getPoints(),
                'profileImagePath' => $profileImageStorage->publicUrl($u->getProfileImagePath()),
            ];
        }

        return $this->json($leaderboard);
    }

    #[Route('/mine', methods: ['GET'])]
    public function getMyAchievements(#[CurrentUser] ?User $user, EntityManagerInterface $em): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $userAchievements = $em->getRepository(UserAchievement::class)->findBy(['user' => $user]);
        
        $badges = [];
        foreach ($userAchievements as $ua) {
            $badges[] = [
                'name' => $ua->getAchievement()->getName(),
                'description' => $ua->getAchievement()->getDescription(),
                'earnedAt' => $ua->getEarnedAt()->format(\DATE_ATOM),
            ];
        }

        return $this->json([
            'points' => $user->getPoints(),
            'badges' => $badges,
        ]);
    }

}