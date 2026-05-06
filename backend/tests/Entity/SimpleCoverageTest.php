<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Session;
use App\Entity\Tag;
use App\Entity\TaskTag;
use App\Entity\NoteTag;
use App\Entity\SubjectTag;

class SimpleCoverageTest extends TestCase
{
    public function testReadMissingLines(): void
    {
        // 1. Hit the missing Session methods
        $session = new Session();
        $session->setBreakMinutes(10);
        $this->assertSame(10, $session->getBreakMinutes());
        
        $session->setDistractionCount(2);
        $this->assertSame(2, $session->getDistractionCount());

        // 2. Hit the missing Tag collection methods
        $tag = new Tag();
        $this->assertIsIterable($tag->getNoteTags());
        $this->assertIsIterable($tag->getTaskTags());
        $this->assertIsIterable($tag->getSubjectTags());
        $this->assertIsIterable($tag->getResourceTags());

        // 3. Hit the missing Tag Lifecycle methods
        $taskTag = new TaskTag();
        $taskTag->setCreatedAtValue();
        $this->assertNotNull($taskTag->getCreatedAt());

        $noteTag = new NoteTag();
        $noteTag->setCreatedAtValue();
        $this->assertNotNull($noteTag->getCreatedAt());

        $subjectTag = new SubjectTag();
        $subjectTag->setCreatedAtValue();
        $this->assertNotNull($subjectTag->getCreatedAt());
    }
}