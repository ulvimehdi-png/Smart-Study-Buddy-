<?php

namespace App\Tests\Service;

use App\Entity\Subject;
use App\Entity\User;
use App\Service\SubjectService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SubjectServiceTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private SubjectService $subjectService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->subjectService = static::getContainer()->get(SubjectService::class);
    }

    public function testGetOrCreateSubjectReturnsNullOnEmptyName(): void
    {
        $user = new User();
        $user->setUsername('student_' . uniqid());
        $user->setPassword('secret');

        // Testing the early return logic when name is empty
        $result = $this->subjectService->getOrCreateSubject($user, '   ');
        $this->assertNull($result);
    }

    public function testGetOrCreateSubjectCreatesNewAndThenFindsExisting(): void
    {
        $user = new User();
        $user->setUsername('student2_' . uniqid());
        $user->setPassword('secret');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // 1. Create a new subject
        $subject1 = $this->subjectService->getOrCreateSubject($user, 'Mathematics');
        $this->assertInstanceOf(Subject::class, $subject1);
        $this->assertSame('Mathematics', $subject1->getName());

        $this->entityManager->flush(); // Save it to the DB so the next step can find it

        // 2. Try to create it again, simulating a user typing it in lowercase
        $subject2 = $this->subjectService->getOrCreateSubject($user, 'mathematics');
        
        // It should return the exact same object from the database, not a duplicate
        $this->assertSame($subject1->getId(), $subject2->getId());
    }
}