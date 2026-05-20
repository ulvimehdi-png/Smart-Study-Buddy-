<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Factory\UserFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class SecurityControllerTest extends ApiWebTestCase
{
    public function testRegisterCreatesUser(): void
    {
        $plainPassword = 'testuser_password';
        $username = $this->uniqueUsername('testuser');

        $this->jsonRequest('POST', '/api/register', [
            'username' => $username,
            'password' => $plainPassword,
        ]);

        self::assertResponseStatusCodeSame(201);

        $data = $this->responseData();

        self::assertSame('register_user_success', $data['status']);
        self::assertSame($username, $data['user']['username']);
        self::assertArrayHasKey('id', $data['user']);
        self::assertArrayHasKey('profileImageUrl', $data['user']);

        $user = $this->entityManager->getRepository(User::class)->find($data['user']['id']);

        self::assertNotNull($user);
        self::assertNotSame($plainPassword, $user->getPassword());
        self::assertTrue($this->passwordHasher->isPasswordValid($user, $plainPassword));
        self::assertNotNull($user->getSettings());
    }

    public function testRegisterRejectsMissingFields(): void
    {
        $cases = [
            [['password' => 'password123']],
            [['username' => 'testuser']],
            [[]],
        ];

        foreach ($cases as [$payload]) {
            $this->jsonRequest('POST', '/api/register', $payload);

            self::assertResponseStatusCodeSame(400);

            $data = $this->responseData();

            self::assertSame('Username and password are required.', $data['error']);
        }
    }

    public function testRegisterRejectsDuplicateUsername(): void
    {
        $username = $this->uniqueUsername('duplicate');

        UserFactory::createOne([
            'username' => $username,
        ]);

        $this->jsonRequest('POST', '/api/register', [
            'username' => $username,
            'password' => 'password123',
        ]);

        self::assertResponseStatusCodeSame(409);

        $data = $this->responseData();

        self::assertSame('Username already exists.', $data['error']);
    }

    public function testRegisterRejectsAlreadyAuthenticatedUser(): void
    {
        $user = $this->loginAs();

        $this->jsonRequest('POST', '/api/register', [
            'username' => $this->uniqueUsername('new_user'),
            'password' => 'password123',
        ]);

        self::assertResponseStatusCodeSame(409);

        $data = $this->responseData();

        self::assertSame('user_already_logged_in', $data['status']);
        self::assertSame($user->getUsername(), $data['user']['username']);
    }

    public function testCurrentUserReturnsNullWhenAnonymous(): void
    {
        $this->client->request('GET', '/api/user');

        self::assertResponseIsSuccessful();

        $data = $this->responseData();

        self::assertNull($data['user']);
    }

    public function testCurrentUserReturnsAuthenticatedUser(): void
    {
        $user = $this->loginAs();

        $this->client->request('GET', '/api/user');

        self::assertResponseIsSuccessful();

        $data = $this->responseData();

        self::assertSame($user->getUsername(), $data['user']['username']);
        self::assertSame($user->getId(), $data['user']['id']);
        self::assertArrayHasKey('profileImageUrl', $data['user']);
    }

    public function testUpdateProfileRejectsAnonymousUser(): void
    {
        $this->jsonRequest('POST', '/api/user/profile', [
            'username' => 'new_name',
        ]);

        self::assertResponseStatusCodeSame(401);

        $data = $this->responseData();

        self::assertSame('Not logged in', $data['error']);
    }

    public function testUpdateProfileRejectsEmptyUsername(): void
    {
        $this->loginAs();

        $this->jsonRequest('POST', '/api/user/profile', [
            'username' => '   ',
        ]);

        self::assertResponseStatusCodeSame(400);

        $data = $this->responseData();

        self::assertSame('Username is required.', $data['error']);
    }

    public function testUpdateProfileRejectsTooShortUsername(): void
    {
        $this->loginAs();

        $this->jsonRequest('POST', '/api/user/profile', [
            'username' => 'ab',
        ]);

        self::assertResponseStatusCodeSame(400);

        $data = $this->responseData();

        self::assertSame('Username must be between 3 and 50 characters.', $data['error']);
    }

    public function testUpdateProfileRejectsTooLongUsername(): void
    {
        $this->loginAs();

        $this->jsonRequest('POST', '/api/user/profile', [
            'username' => str_repeat('a', 51),
        ]);

        self::assertResponseStatusCodeSame(400);

        $data = $this->responseData();

        self::assertSame('Username must be between 3 and 50 characters.', $data['error']);
    }

    public function testUpdateProfileRejectsInvalidCharacters(): void
    {
        $this->loginAs();

        $this->jsonRequest('POST', '/api/user/profile', [
            'username' => 'invalid username!',
        ]);

        self::assertResponseStatusCodeSame(400);

        $data = $this->responseData();

        self::assertSame('Username contains invalid characters.', $data['error']);
    }

    public function testUpdateProfileRejectsDuplicateUsernameFromAnotherUser(): void
    {
        $existing = UserFactory::createOne([
            'username' => $this->uniqueUsername('existing'),
        ]);

        $current = $this->loginAs();

        $this->jsonRequest('POST', '/api/user/profile', [
            'username' => $existing->getUsername(),
        ]);

        self::assertResponseStatusCodeSame(409);

        $data = $this->responseData();

        self::assertSame('Username already exists.', $data['error']);
        self::assertNotSame($existing->getId(), $current->getId());
    }

    public function testUpdateProfileAcceptsSameUsernameForSameUser(): void
    {
        $user = $this->loginAs();

        $this->jsonRequest('POST', '/api/user/profile', [
            'username' => $user->getUsername(),
        ]);

        self::assertResponseIsSuccessful();

        $data = $this->responseData();

        self::assertTrue($data['success']);
        self::assertSame($user->getUsername(), $data['user']['username']);
    }

    public function testUpdateProfileChangesUsername(): void
    {
        $user = $this->loginAs();
        $newUsername = $this->uniqueUsername('renamed');

        $this->jsonRequest('POST', '/api/user/profile', [
            'username' => $newUsername,
        ]);

        self::assertResponseIsSuccessful();

        $data = $this->responseData();

        self::assertTrue($data['success']);
        self::assertSame($newUsername, $data['user']['username']);

        $this->entityManager->refresh($user);

        self::assertSame($newUsername, $user->getUsername());
    }

    public function testUpdatePasswordRejectsAnonymousUser(): void
    {
        $this->jsonRequest('POST', '/api/user/password', [
            'currentPassword' => 'current_password',
            'newPassword' => 'new_password',
            'confirmPassword' => 'new_password',
        ]);

        self::assertResponseStatusCodeSame(401);

        $data = $this->responseData();

        self::assertSame('Not logged in', $data['error']);
    }

    public function testUpdatePasswordRejectsMissingFields(): void
    {
        $this->loginAs(
            $this->createUser(plainPassword: 'current_password')
        );

        $cases = [
            [
                'newPassword' => 'new_password',
                'confirmPassword' => 'new_password',
            ],
            [
                'currentPassword' => 'current_password',
                'confirmPassword' => 'new_password',
            ],
            [
                'currentPassword' => 'current_password',
                'newPassword' => 'new_password',
            ],
        ];

        foreach ($cases as $payload) {
            $this->jsonRequest('POST', '/api/user/password', $payload);

            self::assertResponseStatusCodeSame(400);

            $data = $this->responseData();

            self::assertSame('All password fields are required.', $data['error']);
        }
    }

    public function testUpdatePasswordRejectsWrongCurrentPassword(): void
    {
        $this->loginAs(
            $this->createUser(plainPassword: 'current_password')
        );

        $this->jsonRequest('POST', '/api/user/password', [
            'currentPassword' => 'wrong_password',
            'newPassword' => 'new_password',
            'confirmPassword' => 'new_password',
        ]);

        self::assertResponseStatusCodeSame(400);

        $data = $this->responseData();

        self::assertSame('Current password is incorrect.', $data['error']);
    }

    public function testUpdatePasswordRejectsTooShortNewPassword(): void
    {
        $this->loginAs(
            $this->createUser(plainPassword: 'current_password')
        );

        $this->jsonRequest('POST', '/api/user/password', [
            'currentPassword' => 'current_password',
            'newPassword' => 'short',
            'confirmPassword' => 'short',
        ]);

        self::assertResponseStatusCodeSame(400);

        $data = $this->responseData();

        self::assertSame('New password must be at least 6 characters long.', $data['error']);
    }

    public function testUpdatePasswordRejectsConfirmationMismatch(): void
    {
        $this->loginAs(
            $this->createUser(plainPassword: 'current_password')
        );

        $this->jsonRequest('POST', '/api/user/password', [
            'currentPassword' => 'current_password',
            'newPassword' => 'new_password',
            'confirmPassword' => 'different_password',
        ]);

        self::assertResponseStatusCodeSame(400);

        $data = $this->responseData();

        self::assertSame('New password and confirmation do not match.', $data['error']);
    }

    public function testUpdatePasswordRejectsSamePassword(): void
    {
        $this->loginAs(
            $this->createUser(plainPassword: 'current_password')
        );

        $this->jsonRequest('POST', '/api/user/password', [
            'currentPassword' => 'current_password',
            'newPassword' => 'current_password',
            'confirmPassword' => 'current_password',
        ]);

        self::assertResponseStatusCodeSame(400);

        $data = $this->responseData();

        self::assertSame('New password must be different from the current password.', $data['error']);
    }

    public function testUpdatePasswordChangesPassword(): void
    {
        $user = $this->createUser(plainPassword: 'current_password');

        $this->loginAs($user);

        $this->jsonRequest('POST', '/api/user/password', [
            'currentPassword' => 'current_password',
            'newPassword' => 'new_password',
            'confirmPassword' => 'new_password',
        ]);

        self::assertResponseIsSuccessful();

        $data = $this->responseData();

        self::assertTrue($data['success']);

        $this->entityManager->refresh($user);

        self::assertTrue($this->passwordHasher->isPasswordValid($user, 'new_password'));
        self::assertFalse($this->passwordHasher->isPasswordValid($user, 'current_password'));
    }

    public function testUpdateProfileImageRejectsAnonymousUser(): void
    {
        $this->client->request('POST', '/api/user/profile-image');

        self::assertResponseStatusCodeSame(401);

        $data = $this->responseData();

        self::assertSame('Not logged in', $data['error']);
    }

    public function testUpdateProfileImageRejectsMissingFile(): void
    {
        $this->loginAs();

        $this->client->request('POST', '/api/user/profile-image');

        self::assertResponseStatusCodeSame(400);

        $data = $this->responseData();

        self::assertSame('Profile image file is required.', $data['error']);
    }

    public function testUpdateProfileImageRejectsInvalidUpload(): void
    {
        $this->loginAs();

        $path = tempnam(sys_get_temp_dir(), 'upload_');
        file_put_contents($path, 'partial upload');

        $file = new UploadedFile(
            $path,
            'avatar.png',
            'image/png',
            UPLOAD_ERR_PARTIAL,
            true
        );

        $this->client->request(
            'POST',
            '/api/user/profile-image',
            files: ['profileImage' => $file]
        );

        self::assertResponseStatusCodeSame(400);

        $data = $this->responseData();

        self::assertSame('Uploaded file is invalid.', $data['error']);
    }

    public function testUpdateProfileImageRejectsTooLargeFile(): void
    {
        $this->loginAs();

        $path = tempnam(sys_get_temp_dir(), 'large_avatar_');
        file_put_contents($path, str_repeat('x', (2 * 1024 * 1024) + 1));

        $file = new UploadedFile(
            $path,
            'avatar.png',
            'image/png',
            UPLOAD_ERR_OK,
            true
        );

        $this->client->request(
            'POST',
            '/api/user/profile-image',
            files: ['profileImage' => $file]
        );

        self::assertResponseStatusCodeSame(400);

        $data = $this->responseData();

        self::assertSame('Profile image must be 2 MB or smaller.', $data['error']);
    }

    public function testUpdateProfileImageRejectsUnsupportedMimeType(): void
    {
        $this->loginAs();

        $path = tempnam(sys_get_temp_dir(), 'upload_');
        file_put_contents($path, 'not an image');

        $file = new UploadedFile(
            $path,
            'test.txt',
            'text/plain',
            UPLOAD_ERR_OK,
            true
        );

        $this->client->request(
            'POST',
            '/api/user/profile-image',
            files: ['profileImage' => $file]
        );

        self::assertResponseStatusCodeSame(400);

        $data = $this->responseData();

        self::assertSame('Unsupported image type.', $data['error']);
    }

    public function testUpdateProfileImageAcceptsValidPng(): void
    {
        $user = $this->loginAs();

        $path = tempnam(sys_get_temp_dir(), 'avatar_');

        file_put_contents(
            $path,
            base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=',
                true
            )
        );

        $file = new UploadedFile(
            $path,
            'avatar.png',
            'image/png',
            UPLOAD_ERR_OK,
            true
        );

        $this->client->request(
            'POST',
            '/api/user/profile-image',
            files: ['profileImage' => $file]
        );

        self::assertResponseIsSuccessful();

        $data = $this->responseData();

        self::assertTrue($data['success']);
        self::assertSame($user->getUsername(), $data['user']['username']);

        $this->entityManager->refresh($user);

        self::assertNotNull($user->getProfileImagePath());
    }

    public function testDeleteProfileImageRejectsAnonymousUser(): void
    {
        $this->client->request('DELETE', '/api/user/profile-image');

        self::assertResponseStatusCodeSame(401);

        $data = $this->responseData();

        self::assertSame('Not logged in', $data['error']);
    }

    public function testDeleteProfileImageSucceedsForAuthenticatedUser(): void
    {
        $user = $this->createUser([
            'profileImagePath' => 'profiles/test-avatar.png',
        ]);

        $this->loginAs($user);

        $this->client->request('DELETE', '/api/user/profile-image');

        self::assertResponseIsSuccessful();

        $data = $this->responseData();

        self::assertTrue($data['success']);

        $this->entityManager->refresh($user);

        self::assertNull($user->getProfileImagePath());
    }
}
