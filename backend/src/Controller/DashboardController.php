<?php

namespace App\Controller;

use App\Entity\Note;
use App\Entity\Session;
use App\Entity\Subject;
use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/public/api/dashboard')]
final class DashboardController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function getDashboard(
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $notesCount = (int) $em->getRepository(Note::class)
            ->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.user = :user')
            ->andWhere('n.deletedAt IS NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        $totalMin = (int) $em->createQueryBuilder()
            ->select('COALESCE(SUM(s.actualFocusMinutes), 0)')
            ->from(Session::class, 's')
            ->where('s.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        $todayStart = new \DateTimeImmutable('today');
        $tomorrowStart = $todayStart->modify('+1 day');

        $todayMin = (int) $em->createQueryBuilder()
            ->select('COALESCE(SUM(s.actualFocusMinutes), 0)')
            ->from(Session::class, 's')
            ->where('s.user = :user')
            ->andWhere('s.startedAt >= :todayStart')
            ->andWhere('s.startedAt < :tomorrowStart')
            ->setParameter('user', $user)
            ->setParameter('todayStart', $todayStart)
            ->setParameter('tomorrowStart', $tomorrowStart)
            ->getQuery()
            ->getSingleScalarResult();

        $notes = $em->getRepository(Note::class)
            ->createQueryBuilder('n')
            ->leftJoin('n.subject', 's')
            ->addSelect('s')
            ->where('n.user = :user')
            ->andWhere('n.deletedAt IS NULL')
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults(5)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $recent = [];

        foreach ($notes as $n) {
            $recent[] = [
                'noteId' => $n->getId(),
                'id' => $n->getId(),
                'title' => $n->getTitle(),
                'content' => $n->getContent(),
                'createdAt' => $n->getCreatedAt()?->format(DATE_ATOM),
                'subject' => $n->getSubject()?->getName(),
            ];
        }

        $subjects = $em->getRepository(Subject::class)
            ->createQueryBuilder('s')
            ->where('s.user = :user')
            ->andWhere('s.deletedAt IS NULL')
            ->orderBy('s.name', 'ASC')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $tasks = $em->getRepository(Task::class)
            ->createQueryBuilder('t')
            ->leftJoin('t.subject', 's')
            ->addSelect('s')
            ->where('t.user = :user')
            ->andWhere('t.deletedAt IS NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $progressMap = [];

        foreach ($subjects as $subject) {
            $progressMap[$subject->getId()] = [
                'subject' => $subject->getName(),
                'completedTasks' => 0,
                'totalTasks' => 0,
                'progressPercent' => 0,
            ];
        }

        foreach ($tasks as $task) {
            $subject = $task->getSubject();

            if (!$subject || $subject->isDeleted()) {
                continue;
            }

            $subjectId = $subject->getId();

            if (!isset($progressMap[$subjectId])) {
                continue;
            }

            $progressMap[$subjectId]['totalTasks']++;

            if ($task->getStatus() === TaskStatus::DONE) {
                $progressMap[$subjectId]['completedTasks']++;
            }
        }

        $progress = array_values(array_map(
            static function (array $row): array {
                $total = $row['totalTasks'];
                $completed = $row['completedTasks'];

                $row['progressPercent'] = $total > 0
                    ? round(($completed * 100) / $total, 1)
                    : 0.0;

                return $row;
            },
            $progressMap
        ));

        $sessionHistory = $em->getRepository(Session::class)
            ->createQueryBuilder('s')
            ->where('s.user = :user')
            ->andWhere('s.actualFocusMinutes > 0')
            ->orderBy('s.startedAt', 'DESC')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $studyDates = [];

        foreach ($sessionHistory as $session) {
            $studyDates[$session->getStartedAt()?->format('Y-m-d')] = true;
        }

        $studyStreak = 0;
        $cursor = new \DateTimeImmutable('today');

        while (isset($studyDates[$cursor->format('Y-m-d')])) {
            $studyStreak++;
            $cursor = $cursor->modify('-1 day');
        }

        return $this->json([
            'totalNotes' => $notesCount,
            'totalHours' => round($totalMin / 60, 1),
            'todayHours' => round($todayMin / 60, 1),
            'studyStreak' => $studyStreak,
            'progress' => $progress,
            'recentNotes' => $recent,
            'points' => $user->getPoints()
        ]);
    }
}
