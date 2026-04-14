<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserSettings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api')]
final class SecurityController extends AbstractController
{
    #[Route('/register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        $data = $request->toArray();

        $username = trim((string) ($data['username'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if ('' === $username || '' === $password){
            return $this->json(['error' => 'Username and password are required.'], 400);
        }

        $existing = $em->getRepository(User::class)->findOneBy([
            'username' => $username,
        ]);

        if ($existing) {
            return $this->json(['error' => 'Username already exists.'], 409);
        }

        $user = new User();
        $user->setUsername($username);
        $user->setPassword(
            $hasher->hashPassword($user, $password)
        );
        $user->setIsActive(true);

        $settings = new UserSettings();
        $settings->setUser($user);
        $user->setUserSettings($settings);

        $em->persist($user);
        $em->persist($settings);
        $em->flush();

        return $this->json([
            'status' => 'user created',
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUserIdentifier(),
            ],
        ], 201);
    }

    #[Route('/user', methods: ['GET'])]
    public function getCurrentUser(#[CurrentUser] ?User $user): JsonResponse
    {
        return $this->json([
            'user' => $user ? [
                'id' => $user->getId(),
                'username' => $user->getUserIdentifier(),
            ] : null,
        ]);
    }

    #[Route('/login', methods: ['POST'])]
    public function login(): never
    {
        throw new \LogicException('Handled by Symfony security.');
    }

    #[Route('/logout', methods: ['POST'])]
    public function logout(): never
    {
        throw new \LogicException('Handled by Symfony security.');
    }
}
