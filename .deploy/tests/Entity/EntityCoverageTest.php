<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\User;
use App\Entity\Task;
use App\Entity\Note;
use App\Entity\Subject;
use App\Entity\Settings;
use App\Entity\Achievement;
use App\Entity\Session;
use App\Entity\SessionEntry;
use App\Entity\Resource;
use App\Entity\Tag;
use App\Entity\SubjectTag;
use App\Entity\TaskTag;
use App\Entity\NoteTag;
use App\Entity\NoteAttachment;
use App\Enum\TaskPriority;
use App\Enum\TaskStatus;

class EntityCoverageTest extends TestCase
{
    public function testUserEntity(): void
    {
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('test@test.com');
        $user->setDisplayName('Test User');
        $user->setPoints(10);
        $user->addPoints(5);
        $user->setIsActive(true);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword('hashedpassword');
        $user->setLastLoginAt(new \DateTimeImmutable('2026-01-01'));
        $user->eraseCredentials();

        $this->assertSame('testuser', $user->getUsername());
        $this->assertSame('testuser', $user->getUserIdentifier());
        $this->assertSame('test@test.com', $user->getEmail());
        $this->assertSame('Test User', $user->getDisplayName());
        $this->assertSame(15, $user->getPoints());
        $this->assertTrue($user->isActive());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertSame('hashedpassword', $user->getPassword());
        $this->assertNotNull($user->getLastLoginAt());

        $serialized = $user->__serialize();
        $this->assertIsArray($serialized);

        $subject = new Subject();
        $user->addSubject($subject);
        $this->assertTrue($user->getSubjects()->contains($subject));
        $user->removeSubject($subject);

        $task = new Task();
        $user->addTask($task);
        $this->assertTrue($user->getTasks()->contains($task));
        $user->removeTask($task);

        $session = new Session();
        $user->addSession($session);
        $this->assertTrue($user->getSessions()->contains($session));
        $user->removeSession($session);

        $note = new Note();
        $user->addNote($note);
        $this->assertTrue($user->getNotes()->contains($note));
        $user->removeNote($note);

        $resource = new Resource();
        $user->addResource($resource);
        $this->assertTrue($user->getResources()->contains($resource));
        $user->removeResource($resource);

        $tag = new Tag();
        $user->addTag($tag);
        $this->assertTrue($user->getTags()->contains($tag));
        $user->removeTag($tag);
    }

    public function testSubjectEntity(): void
    {
        $subject = new Subject();
        $subject->setCode('SUB1');
        $subject->setName('Math');
        $subject->setDescription('Mathematics');
        $subject->setColorHex('#FFFFFF');
        $subject->setTargetTotalMinutes(100);
        $subject->setTargetWeeklyMinutes(50);
        $user = new User();
        $subject->setUser($user);

        $this->assertSame('SUB1', $subject->getCode());
        $this->assertSame('Math', $subject->getName());
        $this->assertSame('Mathematics', $subject->getDescription());
        $this->assertSame('#FFFFFF', $subject->getColorHex());
        $this->assertSame(100, $subject->getTargetTotalMinutes());
        $this->assertSame(50, $subject->getTargetWeeklyMinutes());
        $this->assertSame($user, $subject->getUser());

        $task = new Task();
        $subject->addTask($task);
        $this->assertTrue($subject->getTasks()->contains($task));
        $subject->removeTask($task);

        $note = new Note();
        $subject->addNote($note);
        $this->assertTrue($subject->getNotes()->contains($note));
        $subject->removeNote($note);

        $resource = new Resource();
        $subject->addResource($resource);
        $this->assertTrue($subject->getResources()->contains($resource));
        $subject->removeResource($resource);

        $subjectTag = new SubjectTag();
        $subject->addSubjectTag($subjectTag);
        $this->assertTrue($subject->getSubjectTags()->contains($subjectTag));
        $subject->removeSubjectTag($subjectTag);

        $sessionEntry = new SessionEntry();
        $subject->addSessionEntry($sessionEntry);
        $this->assertTrue($subject->getSessionEntries()->contains($sessionEntry));
        $subject->removeSessionEntry($sessionEntry);
    }

    public function testTaskEntity(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Desc');
        $task->setStatus(TaskStatus::TODO);
        $task->setPriority(TaskPriority::HIGH);
        $task->setEstimatedMinutes(30);
        $task->setDueAt(new \DateTimeImmutable('2026-01-01'));
        $task->setScheduledFor(new \DateTimeImmutable('2026-01-01'));
        $user = new User();
        $task->setUser($user);
        $subject = new Subject();
        $task->setSubject($subject);

        $this->assertSame('Test Task', $task->getTitle());
        $this->assertSame('Desc', $task->getDescription());
        $this->assertSame(TaskStatus::TODO, $task->getStatus());
        $this->assertSame(TaskPriority::HIGH, $task->getPriority());
        $this->assertSame(30, $task->getEstimatedMinutes());
        $this->assertNotNull($task->getDueAt());
        $this->assertNotNull($task->getScheduledFor());
        $this->assertSame($user, $task->getUser());
        $this->assertSame($subject, $task->getSubject());

        $task->markDone();
        $this->assertSame(TaskStatus::DONE, $task->getStatus());
        $this->assertNotNull($task->getCompletedAt());

        $note = new Note();
        $task->addNote($note);
        $this->assertTrue($task->getNotes()->contains($note));
        $task->removeNote($note);

        $taskTag = new TaskTag();
        $task->addTaskTag($taskTag);
        $this->assertTrue($task->getTaskTags()->contains($taskTag));
        $task->removeTaskTag($taskTag);

        $sessionEntry = new SessionEntry();
        $task->addSessionEntry($sessionEntry);
        $this->assertTrue($task->getSessionEntries()->contains($sessionEntry));
        $task->removeSessionEntry($sessionEntry);
    }

    public function testNoteEntity(): void
    {
        $note = new Note();
        $note->setTitle('Note Title');
        $note->setContent('Content');
        $note->setExternalUrl('http://test.com');
        $user = new User();
        $note->setUser($user);
        $subject = new Subject();
        $note->setSubject($subject);
        $task = new Task();
        $note->setTask($task);

        $this->assertSame('Note Title', $note->getTitle());
        $this->assertSame('Content', $note->getContent());
        $this->assertSame('http://test.com', $note->getExternalUrl());
        $this->assertSame($user, $note->getUser());
        $this->assertSame($subject, $note->getSubject());
        $this->assertSame($task, $note->getTask());

        $attachment = new NoteAttachment();
        $note->addNoteAttachment($attachment);
        $this->assertTrue($note->getNoteAttachments()->contains($attachment));
        $note->removeNoteAttachment($attachment);

        $noteTag = new NoteTag();
        $note->addNoteTag($noteTag);
        $this->assertTrue($note->getNoteTags()->contains($noteTag));
        $note->removeNoteTag($noteTag);

        $sessionEntry = new SessionEntry();
        $note->addSessionEntry($sessionEntry);
        $this->assertTrue($note->getSessionEntries()->contains($sessionEntry));
        $note->removeSessionEntry($sessionEntry);
    }

    public function testSessionEntity(): void
    {
        $session = new Session();
        $session->setPlannedMinutes(60);
        $session->setActualFocusMinutes(45);
        $session->setBreakMinutes(5);
        $session->setDistractionCount(1);
        $session->setSessionNote('Good');
        $session->setStartedAt(new \DateTimeImmutable());
        $session->setEndedAt(new \DateTimeImmutable());
        $user = new User();
        $session->setUser($user);

        $this->assertSame(60, $session->getPlannedMinutes());
        $this->assertSame(45, $session->getActualFocusMinutes());
        $this->assertSame(5, $session->getBreakMinutes());
        $this->assertSame(1, $session->getDistractionCount());
        $this->assertSame('Good', $session->getSessionNote());
        $this->assertNotNull($session->getStartedAt());
        $this->assertNotNull($session->getEndedAt());
        $this->assertSame($user, $session->getUser());

        $entry = new SessionEntry();
        $session->addSessionEntry($entry);
        $this->assertTrue($session->getSessionEntries()->contains($entry));
        $session->removeSessionEntry($entry);
    }

    public function testSessionEntryEntity(): void
    {
        $entry = new SessionEntry();
        $session = new Session();
        $entry->setSession($session);
        $subject = new Subject();
        $subject->setName('Sub');
        $entry->setSubject($subject);
        $task = new Task();
        $task->setTitle('Tsk');
        $entry->setTask($task);
        $note = new Note();
        $note->setTitle('Nte');
        $entry->setNote($note);
        $entry->setPlannedMinutes(10);
        $entry->setActualFocusMinutes(10);
        $entry->setEntryStartedAt(new \DateTimeImmutable());
        $entry->setEntryEndedAt(new \DateTimeImmutable());
        $entry->setSubjectNameSnapshot('SubSnap');
        $entry->setTaskTitleSnapshot('TskSnap');
        $entry->setNoteTitleSnapshot('NteSnap');

        $this->assertSame($session, $entry->getSession());
        $this->assertSame($subject, $entry->getSubject());
        $this->assertSame($task, $entry->getTask());
        $this->assertSame($note, $entry->getNote());
        $this->assertSame(10, $entry->getPlannedMinutes());
        $this->assertSame(10, $entry->getActualFocusMinutes());
        $this->assertNotNull($entry->getEntryStartedAt());
        $this->assertNotNull($entry->getEntryEndedAt());
        $this->assertSame('SubSnap', $entry->getSubjectNameSnapshot());
        $this->assertSame('TskSnap', $entry->getTaskTitleSnapshot());
        $this->assertSame('NteSnap', $entry->getNoteTitleSnapshot());
    }

    public function testResourceEntity(): void
    {
        $resource = new Resource();
        $resource->setTitle('Res');
        $resource->setDescription('Desc');
        $resource->setExternalUrl('http://res.com');
        $resource->setOriginalFilename('file.txt');
        $resource->setStoragePath('/path');
        $resource->setMimeType('text/plain');
        $resource->setSizeBytes(100);
        $resource->setChecksumSha256(str_repeat('a', 64));
        $user = new User();
        $resource->setUser($user);
        $subject = new Subject();
        $resource->setSubject($subject);

        $this->assertSame('Res', $resource->getTitle());
        $this->assertSame('Desc', $resource->getDescription());
        $this->assertSame('http://res.com', $resource->getExternalUrl());
        $this->assertSame('file.txt', $resource->getOriginalFilename());
        $this->assertSame('/path', $resource->getStoragePath());
        $this->assertSame('text/plain', $resource->getMimeType());
        $this->assertSame(100, $resource->getSizeBytes());
        $this->assertSame(str_repeat('a', 64), $resource->getChecksumSha256());
        $this->assertSame($user, $resource->getUser());
        $this->assertSame($subject, $resource->getSubject());
    }

    public function testTagEntity(): void
    {
        $tag = new Tag();
        $tag->setName('Tag');
        $tag->setColorHex('#000000');
        $user = new User();
        $tag->setUser($user);

        $this->assertSame('Tag', $tag->getName());
        $this->assertSame('#000000', $tag->getColorHex());
        $this->assertSame($user, $tag->getUser());
    }

    public function testSettingsEntity(): void
    {
        $settings = new Settings();
        $user = new User();
        $settings->setUser($user);
        $settings->setTimezone('Europe/Paris');
        $settings->setDefaultSessionMinutes(25);
        $settings->setDefaultBreakMinutes(5);

        $this->assertSame($user, $settings->getUser());
        $this->assertSame('Europe/Paris', $settings->getTimezone());
        $this->assertSame(25, $settings->getDefaultSessionMinutes());
        $this->assertSame(5, $settings->getDefaultBreakMinutes());
    }

    public function testNoteAttachmentEntity(): void
    {
        $attachment = new NoteAttachment();
        $note = new Note();
        $attachment->setNote($note);
        $attachment->setOriginalFilename('file.jpg');
        $attachment->setStoragePath('/img');
        $attachment->setMimeType('image/jpeg');
        $attachment->setSizeBytes(200);
        $attachment->setChecksumSha256(str_repeat('b', 64));

        $this->assertSame($note, $attachment->getNote());
        $this->assertSame('file.jpg', $attachment->getOriginalFilename());
        $this->assertSame('/img', $attachment->getStoragePath());
        $this->assertSame('image/jpeg', $attachment->getMimeType());
        $this->assertSame(200, $attachment->getSizeBytes());
        $this->assertSame(str_repeat('b', 64), $attachment->getChecksumSha256());
    }
}