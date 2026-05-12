<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Entity\Note;
use App\Enum\NoteSourceType;
use Doctrine\ORM\EntityManagerInterface;

class NoteControllerTest extends WebTestCase
{
    public function testGetNotesFailsWhenNotLoggedIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/notes');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetNotesSucceedsWhenLoggedIn(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $testUser = $entityManager->getRepository(User::class)->findOneBy([]);

        $client->loginUser($testUser);
        $client->request('GET', '/api/notes');

        $this->assertResponseIsSuccessful();
    }

    public function testCreateNote(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $testUser = $entityManager->getRepository(User::class)->findOneBy([]);

        $client->loginUser($testUser);
        $client->request('POST', '/api/notes', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title' => 'My Note',
            'content' => 'Note content',
            'sourceType' => 'manual'
        ]));

        $this->assertResponseStatusCodeSame(201);
    }

    public function testDeleteNote(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $testUser = $entityManager->getRepository(User::class)->findOneBy([]);

        $note = new Note();
        $note->setTitle('Delete Note');
        $note->setContent('Content');
        $note->setSourceType(NoteSourceType::MANUAL);
        $note->setUser($testUser);
        $entityManager->persist($note);
        $entityManager->flush();

        $client->loginUser($testUser);
        $client->request('DELETE', '/api/notes/' . $note->getId());

        $this->assertResponseIsSuccessful();
    }
}