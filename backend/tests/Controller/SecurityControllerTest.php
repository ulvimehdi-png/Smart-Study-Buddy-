<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private static $client;

    public function setUp(): void
    {
        self::ensureKernelShutdown();

        $client = self::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testRegisterCreatesUser(): void
    {
        $this->$client->request(
            'POST',
            '/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'username' => 'testuser',
                'password' => 'testuser_password',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('register_user_success', $data['status']);
        self::assertSame('testuser', $data['user']['username']);
        self::assertArrayHasKey('id', $data['user']);
        self::assertArrayHasKey('profileImageUrl', $data['user']);

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'username' => 'testuser',
        ]);

        self::assertNotNull($user);
        // password MUST be hashed in database
        self::assertNotSame('password123', $user->getPassword());
        // settings entity/relationship MUST be established
        self::assertNotNull($user->getSettings());
    }

    public function testRegisterRejectsMissingFields(): void
    {
        $client = static::createClient();

        $content = [
            ['password' => 'password123'], // Missing username
            ['username' => 'testuser'], // Missing password
            [] // Missing both
        ];

        while ($content[0] !== null) {
            $this->$client->request(
                'POST',
                '/api/register',
                server: ['CONTENT_TYPE' => 'application/json'],
                content: json_encode(array_pop($content), JSON_THROW_ON_ERROR)
            );

            self::assertResponseStatusCodeSame(400);

            $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

            self::assertSame('Username and password are required.', $data['error']);
        }
    }

    public function testRegisterRejectsDuplicateUsername(): void
    {
        $client = static::createClient();

        $content = [
            'username' => 'test_duplicate_username',
            'password' => 'password123',
        ];

        $this->$client->request(
            'POST',
            '/api/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($content, JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('register_user_success', $data['status']);

        $this->$client->request(
            'POST',
            '/api/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($content, JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(409);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Username already exists.', $data['error']);
    }

    public function testCurrentUserReturnsNullWhenAnonymous(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/user');

        self::assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertNull($data['user']);
    }

}
