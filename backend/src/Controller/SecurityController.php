<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\Settings;
use App\Service\ProfileImageStorage;
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
    private function serializeUser(User $user, ProfileImageStorage $profileImageStorage): array
    {
        return [
            'id' => $user->getId(),
            'username' => $user->getUserIdentifier(),
            'profileImageUrl' => $profileImageStorage->publicUrl($user->getProfileImagePath()),
        ];
    }

    #[Route('/register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        ProfileImageStorage $profileImageStorage,
    ): JsonResponse {
        $data = $request->toArray();
        $username = trim((string) ($data['username'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        $error = $this->validateUsername($username);
        if ($error !== null) {
            return $this->json(['error' => $error], 400);
        }

        if ('' === $password) {
            return $this->json(['error' => 'Password is required.'], 400);
        }

        $existing = $em->getRepository(User::class)->findOneBy(['username' => $username]);

        if ($existing instanceof User) {
            return $this->json(['error' => 'Username already exists.'], 409);
        }

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($hasher->hashPassword($user, $password));

        $settings = new Settings();
        $settings->setUser($user);
        $user->setSettings($settings);

        $em->persist($user);
        $em->persist($settings);
        $em->flush();

        return $this->json([
            'status' => 'user created',
            'user' => $this->serializeUser($user, $profileImageStorage),
        ], 201);
    }

    #[Route('/user', methods: ['GET'])]
    public function getCurrentUser(
        #[CurrentUser] ?User $user,
        ProfileImageStorage $profileImageStorage,
    ): JsonResponse {
        return $this->json([
            'user' => $user instanceof User ? $this->serializeUser($user, $profileImageStorage) : null,
        ]);
    }

    #[Route('/user/profile', methods: ['PATCH'])]
    public function updateProfile(
        Request $request,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em,
        ProfileImageStorage $profileImageStorage,
    ): JsonResponse {
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $data = $request->toArray();
        $username = trim((string) ($data['username'] ?? ''));

        $error = $this->validateUsername($username);
        if ($error !== null) {
            return $this->json(['error' => $error], 400);
        }

        $existing = $em->getRepository(User::class)->findOneBy(['username' => $username]);

        if ($existing instanceof User && $existing->getId() !== $user->getId()) {
            return $this->json(['error' => 'Username already exists.'], 409);
        }

        $user->setUsername($username);
        $em->flush();

        return $this->json([
            'success' => true,
            'user' => $this->serializeUser($user, $profileImageStorage),
        ]);
    }

    #[Route('/user/profile-image', methods: ['POST'])]
    public function updateProfileImage(
        Request $request,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em,
        ProfileImageStorage $profileImageStorage,
    ): JsonResponse {
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $file = $request->files->get('profileImage');

        if (!$file || !$file->isValid()) {
            return $this->json(['error' => 'A valid profile image file is required.'], 400);
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            return $this->json(['error' => 'Profile image must be 2 MB or smaller.'], 400);
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file->getMimeType() ?? '', $allowedMimeTypes, true)) {
            return $this->json(['error' => 'Unsupported image type.'], 400);
        }

        $path = $profileImageStorage->store($user, $file);
        $user->setProfileImagePath($path);
        $em->flush();

        return $this->json([
            'success' => true,
            'user' => $this->serializeUser($user, $profileImageStorage),
        ]);
    }

    #[Route('/user/profile-image', methods: ['DELETE'])]
    public function deleteProfileImage(
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em,
        ProfileImageStorage $profileImageStorage,
    ): JsonResponse {
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $profileImageStorage->remove($user->getProfileImagePath());
        $user->setProfileImagePath(null);
        $em->flush();

        return $this->json([
            'success' => true,
            'user' => $this->serializeUser($user, $profileImageStorage),
        ]);
    }

    #[Route('/user/password', methods: ['PATCH'])]
    public function updatePassword(
        Request $request,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $data = $request->toArray();
        $currentPassword = (string) ($data['currentPassword'] ?? '');
        $newPassword = (string) ($data['newPassword'] ?? '');
        $confirmPassword = (string) ($data['confirmPassword'] ?? '');

        if ('' === $currentPassword || '' === $newPassword || '' === $confirmPassword) {
            return $this->json(['error' => 'All password fields are required.'], 400);
        }

        if (!$hasher->isPasswordValid($user, $currentPassword)) {
            return $this->json(['error' => 'Current password is incorrect.'], 400);
        }

        if (mb_strlen($newPassword) < 6) {
            return $this->json(['error' => 'New password must be at least 6 characters long.'], 400);
        }

        if ($newPassword !== $confirmPassword) {
            return $this->json(['error' => 'New password and confirmation do not match.'], 400);
        }

        if ($currentPassword === $newPassword) {
            return $this->json(['error' => 'New password must be different from the current password.'], 400);
        }

        $user->setPassword($hasher->hashPassword($user, $newPassword));
        $em->flush();

        return $this->json(['success' => true]);
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

    private function validateUsername(string $username): ?string
{
    if (empty($username)) {
        return 'Username is required.';
    }

    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        return 'Invalid username format.';
    }

    return null;
}
}