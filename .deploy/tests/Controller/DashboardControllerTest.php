<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class DashboardControllerTest extends WebTestCase
{
    public function testGetDashboardFailsWhenNotLoggedIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/dashboard');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetDashboardSucceedsWhenLoggedIn(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $testUser = $entityManager->getRepository(User::class)->findOneBy([]);

        $client->loginUser($testUser);
        $client->request('GET', '/api/dashboard');

        $this->assertResponseIsSuccessful();
    }
}