<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class ApiWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;
    protected UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();

        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    protected function jsonRequest(string $method, string $uri, array $payload = []): void
    {
        $this->client->request(
            $method,
            $uri,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    protected function responseData(): array
    {
        $content = $this->client->getResponse()->getContent();

        self::assertNotFalse($content, 'Response content is false.');
        self::assertJson($content, 'Response is not valid JSON.');

        return json_decode(
            $content,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    protected function uniqueUsername(string $prefix = 'user'): string
    {
        return uniqid($prefix, true);
    }

    protected function createUser(
        array  $attributes = [],
        string $plainPassword = 'current_password'
    ): User
    {
        return UserFactory::new()
            ->withPlainPassword($plainPassword)
            ->with($attributes)
            ->create();
    }

    protected function loginAs(?User $user = null): User
    {
        $user ??= $this->createUser();

        $this->client->loginUser($user);

        return $user;
    }
}
