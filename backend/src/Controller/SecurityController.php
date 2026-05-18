<?php

namespace App\Controller;

use App\Entity\Settings;
use App\Entity\User;
use App\Service\ProfileImageStorage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api')]
final class SecurityController extends AbstractController
{
    public function __construct(
        #[Autowire(service: 'monolog.logger.api')]
        private LoggerInterface $logger
    )
    {
    }

    private function serializeUser(
        User                $user,
        ProfileImageStorage $profileImageStorage
    ): array
    {
        $this->logger->info('User serialized', [
            'user_id' => $user->getId(),
            'username' => $user->getUserIdentifier(),
            'profileImageUrl' => $profileImageStorage->publicUrl($user->getProfileImagePath()),
        ]);

        return [
            'id' => $user->getId(),
            'username' => $user->getUserIdentifier(),
            'profileImageUrl' => $profileImageStorage->publicUrl($user->getProfileImagePath()),
        ];
    }

    #[Route('/register', methods: ['POST'])]
    public function register(
        Request                     $request,
        EntityManagerInterface      $em,
        UserPasswordHasherInterface $hasher,
        ProfileImageStorage         $profileImageStorage,
    ): JsonResponse
    {
        $data = $request->toArray();

        $username = trim((string)($data['username'] ?? ''));
        $password = (string)($data['password'] ?? '');

        if ('' === $username || '' === $password) {
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

        $settings = new Settings();
        $settings->setUser($user);
        $user->setSettings($settings);

        $em->persist($user);
        $em->persist($settings);
        $em->flush();

        $this->logger->info('User registered', [
            'user_id' => $user->getId(),
            'username' => $user->getUsername(),
            'ip' => $request->getClientIp(),
        ]);

        return $this->json([
            'status' => 'user created',
            'user' => $this->serializeUser($user, $profileImageStorage),
        ], 201);
    }

    #[Route('/user', methods: ['GET'])]
    public function getCurrentUser(
        #[CurrentUser] ?User $user,
        ProfileImageStorage  $profileImageStorage,
    ): JsonResponse
    {
        $this->logger->info('Current user is active', [
            'user_id' => $user->getId(),
            'username' => $user->getUsername(),
            'profile_image_path' => $user->getProfileImagePath(),
        ]);

        return $this->json([
            'user' => $user ? $this->serializeUser($user, $profileImageStorage) : null,
        ]);
    }

    #[Route('/user/profile', methods: ['PATCH'])]
    public function updateProfile(
        Request                $request,
        #[CurrentUser] ?User   $user,
        EntityManagerInterface $em,
        ProfileImageStorage    $profileImageStorage,
    ): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $data = $request->toArray();
        $username = trim((string)($data['username'] ?? ''));

        if ('' === $username) {
            return $this->json(['error' => 'Username is required.'], 400);
        }

        if (mb_strlen($username) < 3 || mb_strlen($username) > 50) {
            return $this->json(['error' => 'Username must be between 3 and 50 characters.'], 400);
        }

        if (!preg_match('/^[A-Za-z0-9_.-]+$/', $username)) {
            return $this->json(['error' => 'Username contains invalid characters.'], 400);
        }

        $existing = $em->getRepository(User::class)->findOneBy([
            'username' => $username,
        ]);

        if ($existing && $existing->getId() !== $user->getId()) {

            $this->logger->warning('Registration attempt with existing username', [
                'username' => $username,
                'ip' => $request->getClientIp(),
            ]);

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
        Request                $request,
        #[CurrentUser] ?User   $user,
        EntityManagerInterface $em,
        ProfileImageStorage    $profileImageStorage,
    ): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $file = $request->files->get('profileImage');

        if (!$file) {
            return $this->json(['error' => 'Profile image file is required.'], 400);
        }

        if (!$file->isValid()) {
            return $this->json(['error' => 'Uploaded file is invalid.'], 400);
        }

        $fileSize = $file->getSize();
        if ($fileSize > 2 * 1024 * 1024) {
            return $this->json(['error' => 'Profile image must be 2 MB or smaller.'], 400);
        }

        $mimeType = $file->getMimeType() ?? '';
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            return $this->json(['error' => 'Unsupported image type.'], 400);
        }

        $path = $profileImageStorage->store($user, $file);
        $user->setProfileImagePath($path);
        $em->flush();

        $this->logger->info('User updated profile image', [
            'user_id' => $user->getId(),
            'mime_type' => $mimeType,
            'size' => $fileSize,
        ]);

        return $this->json([
            'success' => true,
            'user' => $this->serializeUser($user, $profileImageStorage),
        ]);
    }

    #[Route('/user/profile-image', methods: ['DELETE'])]
    public function deleteProfileImage(
        #[CurrentUser] ?User   $user,
        EntityManagerInterface $em,
        ProfileImageStorage    $profileImageStorage,
    ): JsonResponse
    {
        if (!$user) {
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
        Request                     $request,
        #[CurrentUser] ?User        $user,
        EntityManagerInterface      $em,
        UserPasswordHasherInterface $hasher
    ): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not logged in'], 401);
        }

        $data = $request->toArray();
        $currentPassword = (string)($data['currentPassword'] ?? '');
        $newPassword = (string)($data['newPassword'] ?? '');
        $confirmPassword = (string)($data['confirmPassword'] ?? '');

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
}
