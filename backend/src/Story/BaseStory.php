<?php

namespace App\Story;

use App\Enum\NoteSourceType;
use App\Enum\ResourceType;
use App\Enum\TaskPriority;
use App\Enum\TaskStatus;
use App\Factory\NoteFactory;
use App\Factory\NoteTagFactory;
use App\Factory\ResourceFactory;
use App\Factory\ResourceTagFactory;
use App\Factory\SessionEntryFactory;
use App\Factory\SessionFactory;
use App\Factory\SubjectFactory;
use App\Factory\SubjectTagFactory;
use App\Factory\TagFactory;
use App\Factory\TaskFactory;
use App\Factory\TaskTagFactory;
use App\Factory\UserFactory;
use App\LiveTimerSession\Enum\SessionStatus;
use App\LiveTimerSession\Enum\SessionType;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'base', groups: ['all'])]
final class BaseStory extends Story
{
    public function build(): void
    {
        MinimalStory::load();

        /*
         * USERS
         *  -> keep deterministic naming
         *  -> user1 already exists from minimal
         */
        $user1 = UserFactory::repository()->findOneBy(['username' => 'user1']);

        $user2 = UserFactory::createOne([
            'username' => 'user2',
            'email' => 'user2@studybuddy.test',
            'displayName' => 'User 2',
        ]);

        $user3 = UserFactory::createOne([
            'username' => 'user3',
            'email' => 'user3@studybuddy.test',
            'displayName' => 'User 3',
        ]);

        $user4 = UserFactory::createOne([
            'username' => 'user4',
            'email' => 'user4@studybuddy.test',
            'displayName' => 'User 4',
        ]);

        $users = [$user1, $user2, $user3, $user4];

        $this->addToPool('users-basic', $user2);
        $this->addToPool('users-basic', $user3);
        $this->addToPool('users-basic', $user4);

        /*
         * SUBJECTS
         *  -> 20 total
         *  -> distributed round-robin over the 4 users
         */
        $subjects = [];
        $subjectsByUser = [
            0 => [],
            1 => [],
            2 => [],
            3 => [],
        ];

        for ($i = 1; $i <= 20; $i++) {
            $userIndex = ($i - 1) % 4;

            $subject = SubjectFactory::createOne([
                'user' => $users[$userIndex],
                'name' => "subject{$i}",
                'code' => "SUBJ{$i}",
                'description' => "description for subject{$i}",
                'colorHex' => sprintf('#%06X', ($i * 123457) % 0xFFFFFF),
                'targetTotalMinutes' => 1000 + ($i * 25),
                'targetWeeklyMinutes' => 100 + ($i * 5),
            ]);

            $subjects[] = $subject;
            $subjectsByUser[$userIndex][] = $subject;
            $this->addToPool('subjects-basic', $subject);
        }

        /*
         * TAGS
         *  -> 8 total
         *  -> distributed round-robin over users
         */
        $tags = [];
        $tagsByUser = [
            0 => [],
            1 => [],
            2 => [],
            3 => [],
        ];

        for ($i = 1; $i <= 8; $i++) {
            $userIndex = ($i - 1) % 4;

            $tag = TagFactory::createOne([
                'user' => $users[$userIndex],
                'name' => "tag{$i}",
                'colorHex' => sprintf('#%06X', ($i * 654321) % 0xFFFFFF),
            ]);

            $tags[] = $tag;
            $tagsByUser[$userIndex][] = $tag;
            $this->addToPool('tags-basic', $tag);
        }

        /*
         * TASKS
         *  -> 15 total
         *  -> deterministic naming and enum states
         */
        $tasks = [];
        $tasksByUser = [
            0 => [],
            1 => [],
            2 => [],
            3 => [],
        ];

        for ($i = 1; $i <= 15; $i++) {
            $userIndex = ($i - 1) % 4;
            $subjectIndex = ($i - 1) % count($subjectsByUser[$userIndex]);
            $subject = $subjectsByUser[$userIndex][$subjectIndex];

            $status = match ($i % 4) {
                1 => TaskStatus::TODO,
                2 => TaskStatus::IN_PROGRESS,
                3 => TaskStatus::DONE,
                0 => TaskStatus::CANCELLED,
            };

            $priority = match ($i % 4) {
                1 => TaskPriority::LOW,
                2 => TaskPriority::MEDIUM,
                3 => TaskPriority::HIGH,
                0 => TaskPriority::URGENT,
            };

            $completedAt = $status === TaskStatus::DONE
                ? new \DateTimeImmutable(sprintf('2026-01-%02d 18:00:00', (($i - 1) % 28) + 1))
                : null;

            $task = TaskFactory::createOne([
                'user' => $users[$userIndex],
                'subject' => $subject,
                'title' => "task{$i}",
                'description' => "description for task{$i}",
                'status' => $status,
                'priority' => $priority,
                'estimatedMinutes' => 30 + ($i * 5),
                'dueAt' => new \DateTimeImmutable(sprintf('2026-02-%02d 12:00:00', (($i - 1) % 28) + 1)),
                'scheduledFor' => new \DateTimeImmutable(sprintf('2026-01-%02d 09:00:00', (($i - 1) % 28) + 1)),
                'completedAt' => $completedAt,
            ]);

            $tasks[] = $task;
            $tasksByUser[$userIndex][] = $task;
            $this->addToPool('tasks-basic', $task);
        }

        /*
         * NOTES
         *  -> 15 total
         *  -> every 5th note is standalone (no subject)
         *  -> every 6th note is a link note
         */
        $notes = [];
        $notesByUser = [
            0 => [],
            1 => [],
            2 => [],
            3 => [],
        ];

        for ($i = 1; $i <= 15; $i++) {
            $userIndex = ($i - 1) % 4;
            $subjectIndex = ($i - 1) % count($subjectsByUser[$userIndex]);
            $taskIndex = ($i - 1) % max(1, count($tasksByUser[$userIndex]));

            $subject = ($i % 5 === 0) ? null : $subjectsByUser[$userIndex][$subjectIndex];
            $task = !empty($tasksByUser[$userIndex]) ? $tasksByUser[$userIndex][$taskIndex] : null;

            $sourceType = ($i % 6 === 0) ? NoteSourceType::LINK : NoteSourceType::MANUAL;
            $externalUrl = ($sourceType === NoteSourceType::LINK)
                ? "https://example.test/note{$i}"
                : null;

            $note = NoteFactory::createOne([
                'user' => $users[$userIndex],
                'subject' => $subject,
                'task' => $task,
                'title' => "note{$i}",
                'content' => "content for note{$i}",
                'sourceType' => $sourceType,
                'externalUrl' => $externalUrl,
            ]);

            $notes[] = $note;
            $notesByUser[$userIndex][] = $note;
            $this->addToPool('notes-basic', $note);
        }

        /*
         * RESOURCES
         *  -> 12 total
         *  -> alternating link/file resources
         */
        $resources = [];
        $resourcesByUser = [
            0 => [],
            1 => [],
            2 => [],
            3 => [],
        ];

        for ($i = 1; $i <= 12; $i++) {
            $userIndex = ($i - 1) % 4;
            $subjectIndex = ($i - 1) % count($subjectsByUser[$userIndex]);
            $subject = $subjectsByUser[$userIndex][$subjectIndex];

            $isFile = ($i % 2 === 0);

            $resource = ResourceFactory::createOne([
                'user' => $users[$userIndex],
                'subject' => $subject,
                'title' => "resource{$i}",
                'description' => "description for resource{$i}",
                'resourceType' => $isFile ? ResourceType::FILE : ResourceType::LINK,
                'externalUrl' => $isFile ? null : "https://example.test/resource{$i}",
                'originalFilename' => $isFile ? "resource{$i}.pdf" : null,
                'storagePath' => $isFile ? "/fixtures/resources/resource{$i}.pdf" : null,
                'mimeType' => $isFile ? 'application/pdf' : null,
                'sizeBytes' => $isFile ? (100000 + ($i * 1000)) : null,
                'checksumSha256' => $isFile ? hash('sha256', "resource{$i}") : null,
            ]);

            $resources[] = $resource;
            $resourcesByUser[$userIndex][] = $resource;
            $this->addToPool('resources-basic', $resource);
        }

        /*
         * SUBJECT TAGS
         *  -> 12 total
         */
        for ($i = 1; $i <= 12; $i++) {
            $subject = $subjects[$i - 1];

            $subjectUserIndex = ($i - 1) % 4;
            $tagIndex = ($i - 1) % count($tagsByUser[$subjectUserIndex]);
            $tag = $tagsByUser[$subjectUserIndex][$tagIndex];

            $subjectTag = SubjectTagFactory::createOne([
                'subject' => $subject,
                'tag' => $tag,
            ]);

            $this->addToPool('subject-tags-basic', $subjectTag);
        }

        /*
         * TASK TAGS
         *  -> 12 total
         */
        for ($i = 1; $i <= 12; $i++) {
            $task = $tasks[$i - 1];

            $taskUserIndex = ($i - 1) % 4;
            $tagIndex = ($i - 1) % count($tagsByUser[$taskUserIndex]);
            $tag = $tagsByUser[$taskUserIndex][$tagIndex];

            $taskTag = TaskTagFactory::createOne([
                'task' => $task,
                'tag' => $tag,
            ]);

            $this->addToPool('task-tags-basic', $taskTag);
        }

        /*
         * NOTE TAGS
         *  -> 12 total
         */
        for ($i = 1; $i <= 12; $i++) {
            $note = $notes[$i - 1];

            $noteUserIndex = ($i - 1) % 4;
            $tagIndex = ($i - 1) % count($tagsByUser[$noteUserIndex]);
            $tag = $tagsByUser[$noteUserIndex][$tagIndex];

            $noteTag = NoteTagFactory::createOne([
                'note' => $note,
                'tag' => $tag,
            ]);

            $this->addToPool('note-tags-basic', $noteTag);
        }

        /*
         * RESOURCE TAGS
         *  -> 8 total
         */
        for ($i = 1; $i <= 8; $i++) {
            $resource = $resources[$i - 1];

            $resourceUserIndex = ($i - 1) % 4;
            $tagIndex = ($i - 1) % count($tagsByUser[$resourceUserIndex]);
            $tag = $tagsByUser[$resourceUserIndex][$tagIndex];

            $resourceTag = ResourceTagFactory::createOne([
                'resource' => $resource,
                'tag' => $tag,
            ]);

            $this->addToPool('resource-tags-basic', $resourceTag);
        }

        /*
         * SESSIONS
         *  -> 8 total
         *  -> distributed round-robin over users
         */
        $sessions = [];
        $sessionsByUser = [
            0 => [],
            1 => [],
            2 => [],
            3 => [],
        ];

        for ($i = 1; $i <= 8; $i++) {
            $userIndex = ($i - 1) % 4;

            $startedAt = new \DateTimeImmutable(sprintf('2026-01-%02d 09:00:00', $i));
            $focusMinutes = 40 + ($i * 5);
            $breakMinutes = 10;

            $session = SessionFactory::createOne([
                'user' => $users[$userIndex],
                'status' => SessionStatus::COMPLETED,
                'sessionType' => ($i % 2 === 0) ? SessionType::POMODORO : SessionType::MANUAL,
                'startedAt' => $startedAt,
                'endedAt' => $startedAt->modify('+' . ($focusMinutes + $breakMinutes) . ' minutes'),
                'plannedMinutes' => $focusMinutes + $breakMinutes,
                'actualFocusMinutes' => $focusMinutes,
                'breakMinutes' => $breakMinutes,
                'distractionCount' => $i % 3,
                'sessionNote' => "session{$i}",
            ]);

            $sessions[] = $session;
            $sessionsByUser[$userIndex][] = $session;
            $this->addToPool('sessions-basic', $session);
        }

        /*
         * SESSION ENTRIES
         *  -> 16 total
         *  -> 2 entries per session
         */
        $entryCounter = 1;

        foreach ($sessions as $sessionIndex => $session) {
            $userIndex = $sessionIndex % 4;

            for ($j = 0; $j < 2; $j++) {
                $subject = $subjectsByUser[$userIndex][($sessionIndex + $j) % count($subjectsByUser[$userIndex])];
                $task = $tasksByUser[$userIndex][($sessionIndex + $j) % count($tasksByUser[$userIndex])];
                $note = $notesByUser[$userIndex][($sessionIndex + $j) % count($notesByUser[$userIndex])];

                $entryStartedAt = new \DateTimeImmutable(sprintf(
                    '2026-01-%02d %02d:00:00',
                    $sessionIndex + 1,
                    9 + ($j * 1)
                ));

                $entry = SessionEntryFactory::createOne([
                    'session' => $session,
                    'subject' => $subject,
                    'task' => $task,
                    'note' => $note,
                    'plannedMinutes' => 25 + ($entryCounter * 2),
                    'actualFocusMinutes' => 20 + ($entryCounter * 2),
                    'entryStartedAt' => $entryStartedAt,
                    'entryEndedAt' => $entryStartedAt->modify('+' . (20 + ($entryCounter * 2)) . ' minutes'),
                ]);

                $this->addToPool('session-entries-basic', $entry);
                $entryCounter++;
            }
        }
    }
}
