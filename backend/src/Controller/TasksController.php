<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskPriority;
use App\Enum\TaskStatus;
use App\Service\SubjectService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/tasks')]
final class TasksController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function getTasks(
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $tasks = $em->getRepository(Task::class)
            ->createQueryBuilder('t')
            ->leftJoin('t.subject', 's')
            ->addSelect('s')
            ->where('t.user = :user')
            ->andWhere('t.deletedAt IS NULL')
            ->orderBy('t.createdAt', 'DESC')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        usort($tasks, static function (Task $a, Task $b): int {
            $aDone = $a->getStatus() === TaskStatus::DONE;
            $bDone = $b->getStatus() === TaskStatus::DONE;

            if ($aDone === $bDone) {
                return $b->getCreatedAt() <=> $a->getCreatedAt();
            }

            return $aDone <=> $bDone;
        });

        $data = [];

        foreach ($tasks as $task) {
            $isCompleted = $task->getStatus() === TaskStatus::DONE;

            $data[] = [
                'taskId' => $task->getId(),
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'isCompleted' => $isCompleted,
                'status' => $task->getStatus()->value,
                'dueDate' => $task->getDueAt()?->format(DATE_ATOM),
                'createdAt' => $task->getCreatedAt()?->format(DATE_ATOM),
                'completedAt' => $task->getCompletedAt()?->format(DATE_ATOM),
                'priority' => $task->getPriority()->value,
                'subject' => $task->getSubject()?->getName(),
            ];
        }

        return $this->json($data);
    }

    #[Route('', methods: ['POST'])]
    public function createTask(
        Request $request,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em,
        SubjectService $subjectService
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $data = $request->toArray();

        $title = trim((string) ($data['title'] ?? ''));
        $description = isset($data['description']) ? trim((string) $data['description']) : null;

        if ('' === $title) {
            return $this->json(['error' => 'Title is required.'], 400);
        }

        $subject = $subjectService->getOrCreateSubject($user, $data['subject'] ?? null);

        $task = new Task();
        $task->setUser($user);
        $task->setSubject($subject);
        $task->setTitle($title);
        $task->setDescription($description);

        if (!empty($data['priority'])) {
            $task->setPriority(TaskPriority::from((string) $data['priority']));
        }

        if (!empty($data['dueDate'])) {
            $task->setDueAt(new \DateTimeImmutable((string) $data['dueDate']));
        }

        $em->persist($task);
        $em->flush();

        return $this->json([
            'success' => true,
            'task' => [
                'taskId' => $task->getId(),
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'isCompleted' => false,
                'status' => $task->getStatus()->value,
                'dueDate' => $task->getDueAt()?->format(DATE_ATOM),
                'createdAt' => $task->getCreatedAt()?->format(DATE_ATOM),
                'completedAt' => null,
                'priority' => $task->getPriority()->value,
                'subject' => $task->getSubject()?->getName(),
            ],
        ], 201);
    }

    #[Route('/{id}/toggle', methods: ['PUT'])]
    public function toggleTask(
        int $id,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $task = $em->getRepository(Task::class)
            ->createQueryBuilder('t')
            ->where('t.id = :id')
            ->andWhere('t.user = :user')
            ->andWhere('t.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }

        if ($task->getStatus() === TaskStatus::DONE) {
            $task->setStatus(TaskStatus::TODO);
            $task->setCompletedAt(null);
        } else {
            $task->setStatus(TaskStatus::DONE);
            $task->setCompletedAt(new \DateTimeImmutable());
        }

        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function deleteTask(
        int $id,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $task = $em->getRepository(Task::class)
            ->createQueryBuilder('t')
            ->where('t.id = :id')
            ->andWhere('t.user = :user')
            ->andWhere('t.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }

        $task->softDelete();
        $em->flush();

        return $this->json(['success' => true]);
    }
}
