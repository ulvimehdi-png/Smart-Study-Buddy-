<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ReportControllerTest extends WebTestCase
{
    public function testGetReportsSucceedsWhenLoggedIn(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $testUser = $entityManager->getRepository(User::class)->findOneBy([]);

        $client->loginUser($testUser);
        $client->request('GET', '/api/reports?period=monthly');

        $this->assertResponseIsSuccessful();
    }
}