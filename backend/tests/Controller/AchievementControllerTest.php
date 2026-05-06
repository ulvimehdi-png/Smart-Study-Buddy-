<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AchievementControllerTest extends WebTestCase
{
    public function testGetLeaderboardSucceedsWhenLoggedIn(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $testUser = $entityManager->getRepository(User::class)->findOneBy([]);

        $client->loginUser($testUser);
        $client->request('GET', '/api/achievements/leaderboard');

        $this->assertResponseIsSuccessful();
    }
    
    public function testGetMyAchievementsFailsWhenNotLoggedIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/achievements/mine');
        $this->assertResponseStatusCodeSame(401);
    }
}