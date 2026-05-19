<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/calendar')]
final class CalendarController extends AbstractController
{
    #[Route('/sessions', methods: ['GET'])]
    public function getSessions(
        Request $request,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $startStr = $request->query->get('start');
        $endStr = $request->query->get('end');

        if (!$startStr || !$endStr) {
            return $this->json(['error' => 'Missing start or end date parameters'], 400);
        }

        try {
            $start = new \DateTimeImmutable($startStr);
            $end = new \DateTimeImmutable($endStr . ' 23:59:59');
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid date format'], 400);
        }

        $sessions = $em->getRepository(Session::class)
            ->createQueryBuilder('s')
            ->where('s.user = :user')
            ->andWhere('s.startedAt >= :start')
            ->andWhere('s.startedAt <= :end')
            ->orderBy('s.startedAt', 'ASC')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();

        $data = [];

        foreach ($sessions as $session) {
            $subjectName = 'General Session';
            $entries = $session->getSessionEntries();
            if (count($entries) > 0) {
                $firstEntry = $entries->first();
                if ($firstEntry && $firstEntry->getSubjectNameSnapshot()) {
                    $subjectName = $firstEntry->getSubjectNameSnapshot();
                }
            }

            $data[] = [
                'id' => $session->getId(),
                'date' => $session->getStartedAt()?->format(DATE_ATOM),
                'subject' => $subjectName,
                'minutes' => $session->getActualFocusMinutes() ?? 0,
            ];
        }

        return $this->json($data);
    }

    #[Route('/tasks', methods: ['GET'])]
    public function getTasks(
        Request $request,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $startStr = $request->query->get('start');
        $endStr = $request->query->get('end');

        if (!$startStr || !$endStr) {
            return $this->json(['error' => 'Missing start or end date parameters'], 400);
        }

        try {
            $start = new \DateTimeImmutable($startStr);
            $end = new \DateTimeImmutable($endStr . ' 23:59:59');
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid date format'], 400);
        }

        $tasks = $em->getRepository(Task::class)
            ->createQueryBuilder('t')
            ->where('t.user = :user')
            ->andWhere('t.deletedAt IS NULL')
            ->andWhere('t.dueAt >= :start')
            ->andWhere('t.dueAt <= :end')
            ->orderBy('t.dueAt', 'ASC')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();

        $data = [];

        foreach ($tasks as $task) {
            $data[] = [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'status' => $task->getStatus()->value,
                'dueDate' => $task->getDueAt()?->format(DATE_ATOM),
            ];
        }

        return $this->json($data);
    }
}
