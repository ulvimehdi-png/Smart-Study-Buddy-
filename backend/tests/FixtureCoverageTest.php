<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\AchievementFixtures;
use App\DataFixtures\AppFixtures;
use App\DataFixtures\NoteAttachmentFixtures;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;

class FixtureCoverageTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $hasher;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testLoadUserFixtures(): void
    {
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'teststudent']);
        if ($existingUser) {
            $this->entityManager->remove($existingUser);
            $this->entityManager->flush();
        }

        $fixture = new UserFixtures($this->hasher);
        
        $this->assertIsArray($fixture::getGroups());
        $this->assertIsArray($fixture->getDependencies());

        $fixture->load($this->entityManager);
        
        $userRepo = $this->entityManager->getRepository(User::class);
        $this->assertGreaterThan(0, count($userRepo->findAll()));
    }

    public function testLoadAchievementFixtures(): void
    {
        $fixture = new AchievementFixtures();
        $fixture->load($this->entityManager);
        $this->assertTrue(true);
    }

    public function testLoadAppFixtures(): void
    {
        $fixture = new AppFixtures();
        $fixture->load($this->entityManager);
        $this->assertTrue(true);
    }

    public function testLoadNoteAttachmentFixtures(): void
    {
        $fixture = new NoteAttachmentFixtures();
        
        $this->assertIsArray($fixture::getGroups());
        $this->assertIsArray($fixture->getDependencies());

        $fixture->load($this->entityManager);
        $this->assertTrue(true);
    }
}