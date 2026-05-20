<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Subject;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/subjects')]
final class SubjectsController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function getSubjects(
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $subjects = $em->getRepository(Subject::class)
            ->createQueryBuilder('s')
            ->where('s.user = :user')
            ->andWhere('s.deletedAt IS NULL')
            ->orderBy('s.name', 'ASC')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $data = array_map(
            static fn(Subject $subject): array => [
                'id' => $subject->getId(),
                'name' => $subject->getName(),
            ],
            $subjects
        );

        return $this->json($data);
    }

    #[Route('/{id}/delete', methods: ['POST'])]
    public function deleteSubject(
        int $id,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $subject = $em->getRepository(Subject::class)->findOneBy([
            'id' => $id,
            'user' => $user,
            'deletedAt' => null
        ]);

        if (!$subject instanceof Subject) {
            return $this->json(['error' => 'Subject not found'], 404);
        }

        $subject->softDelete();
        $em->flush();

        return $this->json(['success' => true]);
    }
}
