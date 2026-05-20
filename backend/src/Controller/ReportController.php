<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/reports')]
final class ReportController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function getReports(
        Request $request,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $period = $request->query->getString('period', 'daily');
        $now = new \DateTimeImmutable();

        $start = match ($period) {
            'weekly' => $now->modify('monday this week')->setTime(0, 0, 0),
            'monthly' => $now->modify('first day of this month')->setTime(0, 0, 0),
            default => $now->setTime(0, 0, 0),
        };

        $tasks = $em->getRepository(Task::class)->createQueryBuilder('t')
            ->leftJoin('t.subject', 's')
            ->addSelect('s')
            ->where('t.user = :user')
            ->andWhere('t.status = :status')
            ->andWhere('t.completedAt >= :start')
            ->andWhere('t.deletedAt IS NULL')
            ->setParameter('user', $user)
            ->setParameter('status', TaskStatus::DONE)
            ->setParameter('start', $start)
            ->getQuery()
            ->getResult();

        $subjectStats = [];
        foreach ($tasks as $task) {
            $subjName = $task->getSubject()?->getName() ?? 'No Subject';
            $subjectStats[$subjName] = ($subjectStats[$subjName] ?? 0) + 1;
        }

        $formattedStats = [];
        foreach ($subjectStats as $subject => $count) {
            $formattedStats[] = ['subject' => $subject, 'count' => $count];
        }

        $totalCompleted = count($tasks);

        return $this->json([
            'period' => $period,
            'totalTasks' => $totalCompleted,
            'pointsEarned' => $totalCompleted * 10,
            'subjects' => $formattedStats
        ]);
    }
}