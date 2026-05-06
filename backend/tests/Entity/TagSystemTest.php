<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Tag;
use App\Entity\Task;
use App\Entity\TaskTag;
use App\Entity\Note;
use App\Entity\NoteTag;
use App\Entity\Subject;
use App\Entity\SubjectTag;

class TagSystemTest extends TestCase
{
    public function testTaskTagRelationship(): void
    {
        $task = new Task();
        $tag = new Tag();
        $taskTag = new TaskTag();

        $taskTag->setTask($task);
        $taskTag->setTag($tag);
        $taskTag->setCreatedAtValue(); // Tests the lifecycle callback manually

        $this->assertSame($task, $taskTag->getTask());
        $this->assertSame($tag, $taskTag->getTag());
        $this->assertNotNull($taskTag->getCreatedAt());
    }

    public function testNoteTagRelationship(): void
    {
        $note = new Note();
        $tag = new Tag();
        $noteTag = new NoteTag();

        $noteTag->setNote($note);
        $noteTag->setTag($tag);
        $noteTag->setCreatedAtValue();

        $this->assertSame($note, $noteTag->getNote());
        $this->assertSame($tag, $noteTag->getTag());
        $this->assertNotNull($noteTag->getCreatedAt());
    }

    public function testSubjectTagRelationship(): void
    {
        $subject = new Subject();
        $tag = new Tag();
        $subjectTag = new SubjectTag();

        $subjectTag->setSubject($subject);
        $subjectTag->setTag($tag);
        $subjectTag->setCreatedAtValue();

        $this->assertSame($subject, $subjectTag->getSubject());
        $this->assertSame($tag, $subjectTag->getTag());
        $this->assertNotNull($subjectTag->getCreatedAt());
    }
}