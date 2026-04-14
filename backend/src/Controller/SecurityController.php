<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class SecurityController extends AbstractController
{
    #[Route('/register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setUsername($data['username']);
        $user->setPassword(
            $hasher->hashPassword($user, $data['password'])
        );
        $user->setIsActive(true);

        $em->persist($user);
        $em->flush();

        return $this->json(['status' => 'user created']);
    }

    #[Route('/user', methods: ['GET'])]
    public function getCurrentUser(): JsonResponse
    {
        $user = $this->getUser();

        return $this->json([
            'user' => $user ? [
                'id' => $user->getId(),
                'username' => $user->getUserIdentifier(),
            ] : null
        ]);
    }

    #[Route('/login', methods: ['POST'])]
    public function login(): never
    {
        throw new \LogicException('This should never be reached.');
    }

    #[Route('/logout', methods: ['POST'])]
    public function logout(): never
    {
        throw new \LogicException('This should never be reached.');
    }
}
