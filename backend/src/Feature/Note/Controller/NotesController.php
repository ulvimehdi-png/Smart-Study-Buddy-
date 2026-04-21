<?php

namespace App\Feature\Note\Controller;

use App\Feature\Note\Entity\Note;
use App\Feature\Note\Enum\NoteSourceType;
use App\Feature\Subject\Service\SubjectService;
use App\Feature\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/notes')]
final class NotesController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function getNotes(
        #[CurrentUser] ?User   $user,
        EntityManagerInterface $em
    ): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $notes = $em->getRepository(Note::class)
            ->createQueryBuilder('n')
            ->leftJoin('n.subject', 's')
            ->addSelect('s')
            ->where('n.user = :user')
            ->andWhere('n.deletedAt IS NULL')
            ->orderBy('n.createdAt', 'DESC')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $data = [];

        foreach ($notes as $note) {
            $data[] = [
                'noteId' => $note->getId(),
                'id' => $note->getId(),
                'title' => $note->getTitle(),
                'content' => $note->getContent(),
                'subject' => $note->getSubject()?->getName(),
                'createdAt' => $note->getCreatedAt()?->format(DATE_ATOM),
            ];
        }

        return $this->json($data);
    }

    #[Route('', methods: ['POST'])]
    public function createNote(
        Request                $request,
        #[CurrentUser] ?User   $user,
        EntityManagerInterface $em,
        SubjectService         $subjectService
    ): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $data = $request->toArray();

        $title = trim((string)($data['title'] ?? ''));
        $content = trim((string)($data['content'] ?? ''));

        if ('' === $title || '' === $content) {
            return $this->json(['error' => 'Title and content are required.'], 400);
        }

        $subject = $subjectService->getOrCreateSubject($user, $data['subject'] ?? null);

        $note = new Note();
        $note->setUser($user);
        $note->setSubject($subject);
        $note->setTitle($title);
        $note->setContent($content);
        $note->setSourceType(NoteSourceType::MANUAL);

        $em->persist($note);
        $em->flush();

        return $this->json([
            'success' => true,
            'note' => [
                'noteId' => $note->getId(),
                'id' => $note->getId(),
                'title' => $note->getTitle(),
                'content' => $note->getContent(),
                'subject' => $note->getSubject()?->getName(),
                'createdAt' => $note->getCreatedAt()?->format(DATE_ATOM),
            ],
        ], 201);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function deleteNote(
        int                    $id,
        #[CurrentUser] ?User   $user,
        EntityManagerInterface $em
    ): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $note = $em->getRepository(Note::class)
            ->createQueryBuilder('n')
            ->where('n.id = :id')
            ->andWhere('n.user = :user')
            ->andWhere('n.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$note) {
            return $this->json(['error' => 'Note not found'], 404);
        }

        $note->softDelete();
        $em->flush();

        return $this->json(['success' => true]);
    }
}
