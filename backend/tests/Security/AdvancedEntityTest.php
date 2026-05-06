<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Subject;
use App\Entity\Session;
use App\Entity\SessionEntry;
use App\Entity\Resource;
use App\Entity\Note;
use App\Entity\UserAchievement;
use App\Entity\User;
use App\Entity\Achievement;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AdvancedEntityTest extends TestCase
{
    public function testArchivableSoftDeletableTrait(): void
    {
        $subject = new Subject();
        
        $this->assertFalse($subject->isArchived(), 'Entities should not be archived by default.');
        $subject->archive();
        $this->assertTrue($subject->isArchived(), 'Archive method should set the archived state.');
        $this->assertNotNull($subject->getArchivedAt(), 'Archived timestamp should be set.');
        
        $subject->unarchive();
        $this->assertFalse($subject->isArchived());
        $this->assertNull($subject->getArchivedAt());
        
        $this->assertFalse($subject->isDeleted(), 'Entities should not be soft-deleted by default.');
        $subject->softDelete();
        $this->assertTrue($subject->isDeleted(), 'Soft delete method should set the deleted state.');
        $this->assertNotNull($subject->getDeletedAt(), 'Deleted timestamp should be set.');
        
        $subject->restore();
        $this->assertFalse($subject->isDeleted());
        $this->assertNull($subject->getDeletedAt());
    }

    public function testSessionChronologyValidation(): void
    {
        $session = new Session();
        // Create an invalid timeline where the session ends before it begins
        $session->setStartedAt(new \DateTimeImmutable('2026-05-01 10:00:00'));
        $session->setEndedAt(new \DateTimeImmutable('2026-05-01 09:00:00'));
        
        $context = $this->createMock(ExecutionContextInterface::class);
        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);
        
        $context->expects($this->once())->method('buildViolation')->willReturn($builder);
        $builder->expects($this->once())->method('atPath')->willReturn($builder);
        $builder->expects($this->once())->method('addViolation');
        
        $session->validateChronology($context);
    }

    public function testSessionEntryChronologyValidation(): void
    {
        $entry = new SessionEntry();
        $entry->setEntryStartedAt(new \DateTimeImmutable('2026-05-01 10:00:00'));
        $entry->setEntryEndedAt(new \DateTimeImmutable('2026-05-01 09:00:00'));
        
        $context = $this->createMock(ExecutionContextInterface::class);
        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);
        
        $context->expects($this->once())->method('buildViolation')->willReturn($builder);
        $builder->expects($this->once())->method('atPath')->willReturn($builder);
        $builder->expects($this->once())->method('addViolation');
        
        $entry->validateChronology($context);
    }

    public function testResourceValidationMissingUrl(): void
    {
        $resource = new Resource();
        // Using the built-in backed Enum 'from' method to safely test the string value
        $resource->setResourceType(\App\Enum\ResourceType::from('link'));
        $resource->setExternalUrl(''); // Should trigger a violation
        
        $context = $this->createMock(ExecutionContextInterface::class);
        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);
        
        $context->expects($this->once())->method('buildViolation')->willReturn($builder);
        $builder->expects($this->once())->method('atPath')->willReturn($builder);
        $builder->expects($this->once())->method('addViolation');
        
        $resource->validateResourceFields($context);
    }

    public function testUserAchievementRelationship(): void
    {
        $userAchievement = new UserAchievement();
        $user = new User();
        $achievement = new Achievement();
        
        $userAchievement->setUser($user);
        $userAchievement->setAchievement($achievement);
        
        $this->assertSame($user, $userAchievement->getUser());
        $this->assertSame($achievement, $userAchievement->getAchievement());
        $this->assertNotNull($userAchievement->getEarnedAt(), 'Earned timestamp should be set in constructor.');
        $this->assertNull($userAchievement->getId());
    }
}