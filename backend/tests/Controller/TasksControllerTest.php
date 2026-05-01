<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;

class TasksControllerTest extends WebTestCase
{
    public function testGetTasksFailsWhenNotLoggedIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/tasks');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetTasksSucceedsWhenLoggedIn(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $testUser = $entityManager->getRepository(User::class)->findOneBy([]);

        $client->loginUser($testUser);
        $client->request('GET', '/api/tasks');

        $this->assertResponseIsSuccessful();
    }

    public function testCreateTask(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $testUser = $entityManager->getRepository(User::class)->findOneBy([]);

        $client->loginUser($testUser);
        $client->request('POST', '/api/tasks', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title' => 'My New Task',
            'description' => 'Task description'
        ]));

        $this->assertResponseStatusCodeSame(201);
    }

    public function testToggleTask(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $testUser = $entityManager->getRepository(User::class)->findOneBy([]);

        $task = new Task();
        $task->setTitle('Toggle Task');
        $task->setUser($testUser);
        $entityManager->persist($task);
        $entityManager->flush();

        $client->loginUser($testUser);
        $client->request('PUT', '/api/tasks/' . $task->getId() . '/toggle');

        $this->assertResponseIsSuccessful();
    }

    public function testDeleteTask(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $testUser = $entityManager->getRepository(User::class)->findOneBy([]);

        $task = new Task();
        $task->setTitle('Delete Task');
        $task->setUser($testUser);
        $entityManager->persist($task);
        $entityManager->flush();

        $client->loginUser($testUser);
        $client->request('DELETE', '/api/tasks/' . $task->getId());

        $this->assertResponseIsSuccessful();
    }
}