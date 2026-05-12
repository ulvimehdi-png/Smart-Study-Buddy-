<?php

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
        if (!$user) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        // 1. Find out what timeframe the user wants (daily by default)
        $period = $request->query->get('period', 'daily');
        $start = new \DateTimeImmutable();

        if ($period === 'daily') {
            $start = $start->setTime(0, 0, 0); // Midnight today
        } elseif ($period === 'weekly') {
            $start = $start->modify('monday this week')->setTime(0, 0, 0); // Monday morning
        } elseif ($period === 'monthly') {
            $start = $start->modify('first day of this month')->setTime(0, 0, 0); // 1st of the month
        }

        // 2. Get all tasks finished after that start time
        $tasks = $em->getRepository(Task::class)->createQueryBuilder('t')
            ->leftJoin('t.subject', 's')->addSelect('s')
            ->where('t.user = :user')
            ->andWhere('t.status = :status')
            ->andWhere('t.completedAt >= :start')
            ->andWhere('t.deletedAt IS NULL')
            ->setParameter('user', $user)
            ->setParameter('status', TaskStatus::DONE)
            ->setParameter('start', $start)
            ->getQuery()
            ->getResult();

        // 3. Count them up by subject
        $subjectStats = [];
        $totalCompleted = 0;

        foreach ($tasks as $task) {
            $subjName = $task->getSubject() ? $task->getSubject()->getName() : 'No Subject';
            if (!isset($subjectStats[$subjName])) {
                $subjectStats[$subjName] = 0;
            }
            $subjectStats[$subjName]++;
            $totalCompleted++;
        }

        // Format for the website
        $formattedStats = [];
        foreach ($subjectStats as $subject => $count) {
            $formattedStats[] = ['subject' => $subject, 'count' => $count];
        }

        // 4. Send the data back (10 points per finished task)
        return $this->json([
            'period' => $period,
            'totalTasks' => $totalCompleted,
            'pointsEarned' => $totalCompleted * 10,
            'subjects' => $formattedStats
        ]);
    }
}