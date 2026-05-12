<?php

namespace App\Story;

use Zenstruck\Foundry\Story;

final class DemoStory extends Story
{
    public function build(): void
    {
        MinimalStory::load();

        $users = [
            'emma' => UserFactory::createOne([
                'username' => 'emma',
                'email' => 'emma@studybuddy.test',
                'roles' => ['ROLE_USER'],
                'password' => 'secret',
                'displayName' => 'Emma Verstraeten',
                'profileImagePath' => null,
                'isActive' => true,
                'lastLoginAt' => new \DateTimeImmutable('-30 minutes'),
                'points' => 840,
                'createdAt' => new \DateTimeImmutable('-60 days'),
                'updatedAt' => new \DateTimeImmutable('-30 minutes'),
            ]),
            'noah' => UserFactory::createOne([
                'username' => 'noah',
                'email' => 'noah@studybuddy.test',
                'roles' => ['ROLE_USER'],
                'password' => 'secret',
                'displayName' => 'Noah Peeters',
                'profileImagePath' => null,
                'isActive' => true,
                'lastLoginAt' => new \DateTimeImmutable('-3 hours'),
                'points' => 430,
                'createdAt' => new \DateTimeImmutable('-45 days'),
                'updatedAt' => new \DateTimeImmutable('-3 hours'),
            ]),
        ];

        foreach ($users as $key => $user) {
            $this->addState("demo-user-{$key}", $user);
            $this->addToPool('users-demo', [$user]);

            SettingsFactory::createOne([
                'user' => $user,
                'timezone' => 'Europe/Brussels',
                'weekStartDay' => self::enumCase(WeekStartDay::class, ['MONDAY'], ['monday', 'mon']),
                'defaultSessionType' => self::enumCase(SessionType::class, ['FOCUS', 'POMODORO'], ['focus', 'pomodoro']),
                'defaultSessionMinutes' => 50,
                'defaultBreakMinutes' => 10,
                'createdAt' => new \DateTimeImmutable('-45 days'),
                'updatedAt' => new \DateTimeImmutable('-1 day'),
            ]);
        }

        UserAchievementFactory::createOne([
            'user' => $users['emma'],
            'achievement' => MinimalStory::get('achievement1'),
            'earnedAt' => new \DateTimeImmutable('-12 days'),
        ]);

        UserAchievementFactory::createOne([
            'user' => $users['emma'],
            'achievement' => MinimalStory::get('achievement2'),
            'earnedAt' => new \DateTimeImmutable('-5 days'),
        ]);

        $subjects = [
            'power-electronics' => SubjectFactory::createOne([
                'user' => $users['emma'],
                'code' => 'ELEC-PE',
                'name' => 'Power Electronics',
                'description' => 'Converter design, LT8711 zeta topology, measurements and report preparation.',
                'colorHex' => '#2F6FED',
                'targetTotalMinutes' => 2400,
                'targetWeeklyMinutes' => 420,
                'createdAt' => new \DateTimeImmutable('-40 days'),
                'updatedAt' => new \DateTimeImmutable('-1 day'),
                'archivedAt' => null,
                'deletedAt' => null,
            ]),
            'web-technology' => SubjectFactory::createOne([
                'user' => $users['emma'],
                'code' => 'ICT-WEB',
                'name' => 'Software & Web Technology',
                'description' => 'Symfony backend, React frontend, StudyBuddy feature delivery and testing.',
                'colorHex' => '#1F9D55',
                'targetTotalMinutes' => 3000,
                'targetWeeklyMinutes' => 480,
                'createdAt' => new \DateTimeImmutable('-38 days'),
                'updatedAt' => new \DateTimeImmutable('-2 days'),
                'archivedAt' => null,
                'deletedAt' => null,
            ]),
            'machine-learning' => SubjectFactory::createOne([
                'user' => $users['emma'],
                'code' => 'ICT-ML',
                'name' => 'Machine Learning',
                'description' => 'Dry Bean classification, decision trees, neural networks and reproducible evaluation.',
                'colorHex' => '#8B5CF6',
                'targetTotalMinutes' => 2100,
                'targetWeeklyMinutes' => 360,
                'createdAt' => new \DateTimeImmutable('-35 days'),
                'updatedAt' => new \DateTimeImmutable('-3 days'),
                'archivedAt' => null,
                'deletedAt' => null,
            ]),
        ];

        foreach ($subjects as $key => $subject) {
            $this->addState("demo-subject-{$key}", $subject);
            $this->addToPool('subjects-demo', [$subject]);
        }

        $tags = [
            'exam' => TagFactory::createOne([
                'user' => $users['emma'],
                'name' => 'exam',
                'colorHex' => '#DC2626',
                'createdAt' => new \DateTimeImmutable('-35 days'),
                'updatedAt' => new \DateTimeImmutable('-1 day'),
            ]),
            'lab' => TagFactory::createOne([
                'user' => $users['emma'],
                'name' => 'lab',
                'colorHex' => '#2563EB',
                'createdAt' => new \DateTimeImmutable('-35 days'),
                'updatedAt' => new \DateTimeImmutable('-1 day'),
            ]),
            'report' => TagFactory::createOne([
                'user' => $users['emma'],
                'name' => 'report',
                'colorHex' => '#9333EA',
                'createdAt' => new \DateTimeImmutable('-35 days'),
                'updatedAt' => new \DateTimeImmutable('-1 day'),
            ]),
            'backend' => TagFactory::createOne([
                'user' => $users['emma'],
                'name' => 'backend',
                'colorHex' => '#059669',
                'createdAt' => new \DateTimeImmutable('-35 days'),
                'updatedAt' => new \DateTimeImmutable('-1 day'),
            ]),
        ];

        foreach ($tags as $key => $tag) {
            $this->addState("demo-tag-{$key}", $tag);
            $this->addToPool('tags-demo', [$tag]);
        }

        SubjectTagFactory::createOne([
            'subject' => $subjects['power-electronics'],
            'tag' => $tags['lab'],
            'createdAt' => new \DateTimeImmutable('-30 days'),
        ]);

        SubjectTagFactory::createOne([
            'subject' => $subjects['power-electronics'],
            'tag' => $tags['report'],
            'createdAt' => new \DateTimeImmutable('-30 days'),
        ]);

        SubjectTagFactory::createOne([
            'subject' => $subjects['web-technology'],
            'tag' => $tags['backend'],
            'createdAt' => new \DateTimeImmutable('-30 days'),
        ]);

        SubjectTagFactory::createOne([
            'subject' => $subjects['machine-learning'],
            'tag' => $tags['exam'],
            'createdAt' => new \DateTimeImmutable('-30 days'),
        ]);

        $tasks = [
            'zeta-report' => TaskFactory::createOne([
                'user' => $users['emma'],
                'subject' => $subjects['power-electronics'],
                'title' => 'Finish zeta converter measurement section',
                'description' => 'Add efficiency table, ripple method and thermal observations.',
                'status' => self::enumCase(TaskStatus::class, ['IN_PROGRESS', 'ACTIVE', 'TODO'], ['in_progress', 'active', 'todo']),
                'priority' => self::enumCase(TaskPriority::class, ['HIGH'], ['high']),
                'estimatedMinutes' => 180,
                'dueAt' => new \DateTimeImmutable('+3 days'),
                'scheduledFor' => new \DateTimeImmutable('+1 day'),
                'completedAt' => null,
                'createdAt' => new \DateTimeImmutable('-6 days'),
                'updatedAt' => new \DateTimeImmutable('-2 hours'),
                'archivedAt' => null,
                'deletedAt' => null,
            ]),
            'studycontent-upload' => TaskFactory::createOne([
                'user' => $users['emma'],
                'subject' => $subjects['web-technology'],
                'title' => 'Implement StudyContent resource upload flow',
                'description' => 'Backend endpoint, metadata persistence, frontend test page and validation feedback.',
                'status' => self::enumCase(TaskStatus::class, ['TODO', 'OPEN', 'PENDING'], ['todo', 'open', 'pending']),
                'priority' => self::enumCase(TaskPriority::class, ['HIGH'], ['high']),
                'estimatedMinutes' => 240,
                'dueAt' => new \DateTimeImmutable('+2 days'),
                'scheduledFor' => new \DateTimeImmutable('+12 hours'),
                'completedAt' => null,
                'createdAt' => new \DateTimeImmutable('-4 days'),
                'updatedAt' => new \DateTimeImmutable('-1 hour'),
                'archivedAt' => null,
                'deletedAt' => null,
            ]),
            'ml-evaluation' => TaskFactory::createOne([
                'user' => $users['emma'],
                'subject' => $subjects['machine-learning'],
                'title' => 'Freeze macro-F1 evaluation table',
                'description' => 'Compare decision tree and MLP results on validation/test splits.',
                'status' => self::enumCase(TaskStatus::class, ['DONE', 'COMPLETED'], ['done', 'completed']),
                'priority' => self::enumCase(TaskPriority::class, ['MEDIUM', 'NORMAL'], ['medium', 'normal']),
                'estimatedMinutes' => 120,
                'dueAt' => new \DateTimeImmutable('-1 day'),
                'scheduledFor' => new \DateTimeImmutable('-2 days'),
                'completedAt' => new \DateTimeImmutable('-1 day'),
                'createdAt' => new \DateTimeImmutable('-10 days'),
                'updatedAt' => new \DateTimeImmutable('-1 day'),
                'archivedAt' => null,
                'deletedAt' => null,
            ]),
        ];

        foreach ($tasks as $key => $task) {
            $this->addState("demo-task-{$key}", $task);
            $this->addToPool('tasks-demo', [$task]);
        }

        TaskTagFactory::createOne([
            'task' => $tasks['zeta-report'],
            'tag' => $tags['report'],
            'createdAt' => new \DateTimeImmutable('-5 days'),
        ]);

        TaskTagFactory::createOne([
            'task' => $tasks['studycontent-upload'],
            'tag' => $tags['backend'],
            'createdAt' => new \DateTimeImmutable('-4 days'),
        ]);

        TaskTagFactory::createOne([
            'task' => $tasks['ml-evaluation'],
            'tag' => $tags['exam'],
            'createdAt' => new \DateTimeImmutable('-8 days'),
        ]);

        $notes = [
            'zeta-measurements' => NoteFactory::createOne([
                'user' => $users['emma'],
                'subject' => $subjects['power-electronics'],
                'task' => $tasks['zeta-report'],
                'title' => 'Zeta converter measurement notes',
                'content' => "Key measurements:\n\n- Check efficiency at Vin = 5 V, 12 V, 18 V.\n- Keep thermal and ripple sections separated.\n- Include probe setup notes.",
                'sourceType' => self::enumCase(NoteSourceType::class, ['MANUAL', 'TEXT'], ['manual', 'text']),
                'externalUrl' => null,
                'createdAt' => new \DateTimeImmutable('-5 days'),
                'updatedAt' => new \DateTimeImmutable('-1 hour'),
                'archivedAt' => null,
                'deletedAt' => null,
            ]),
            'symfony-plan' => NoteFactory::createOne([
                'user' => $users['emma'],
                'subject' => $subjects['web-technology'],
                'task' => $tasks['studycontent-upload'],
                'title' => 'StudyContent implementation plan',
                'content' => "Implementation slice:\n\n1. Upload controller.\n2. Resource entity persistence.\n3. Test page in React.\n4. Fixtures for realistic resources.",
                'sourceType' => self::enumCase(NoteSourceType::class, ['MANUAL', 'TEXT'], ['manual', 'text']),
                'externalUrl' => null,
                'createdAt' => new \DateTimeImmutable('-4 days'),
                'updatedAt' => new \DateTimeImmutable('-2 hours'),
                'archivedAt' => null,
                'deletedAt' => null,
            ]),
            'dry-bean-results' => NoteFactory::createOne([
                'user' => $users['emma'],
                'subject' => $subjects['machine-learning'],
                'task' => $tasks['ml-evaluation'],
                'title' => 'Dry Bean evaluation summary',
                'content' => "Report macro-F1 as primary metric. Accuracy remains secondary. Keep test data untouched until final evaluation.",
                'sourceType' => self::enumCase(NoteSourceType::class, ['MANUAL', 'TEXT'], ['manual', 'text']),
                'externalUrl' => null,
                'createdAt' => new \DateTimeImmutable('-8 days'),
                'updatedAt' => new \DateTimeImmutable('-1 day'),
                'archivedAt' => null,
                'deletedAt' => null,
            ]),
        ];

        foreach ($notes as $key => $note) {
            $this->addState("demo-note-{$key}", $note);
            $this->addToPool('notes-demo', [$note]);
        }

        NoteTagFactory::createOne([
            'note' => $notes['zeta-measurements'],
            'tag' => $tags['lab'],
            'createdAt' => new \DateTimeImmutable('-5 days'),
        ]);

        NoteTagFactory::createOne([
            'note' => $notes['symfony-plan'],
            'tag' => $tags['backend'],
            'createdAt' => new \DateTimeImmutable('-4 days'),
        ]);

        NoteTagFactory::createOne([
            'note' => $notes['dry-bean-results'],
            'tag' => $tags['exam'],
            'createdAt' => new \DateTimeImmutable('-8 days'),
        ]);

        NoteAttachmentFactory::createOne([
            'note' => $notes['zeta-measurements'],
            'originalFilename' => 'zeta-efficiency-table.pdf',
            'storagePath' => 'fixtures/demo/zeta-efficiency-table.pdf',
            'mimeType' => 'application/pdf',
            'sizeBytes' => 384000,
            'checksumSha256' => hash('sha256', 'zeta-efficiency-table.pdf'),
            'createdAt' => new \DateTimeImmutable('-5 days'),
            'updatedAt' => new \DateTimeImmutable('-5 days'),
        ]);

        $resources = [
            'lt8711-datasheet' => ResourceFactory::createOne([
                'user' => $users['emma'],
                'subject' => $subjects['power-electronics'],
                'title' => 'LT8711 datasheet',
                'description' => 'Controller reference for zeta converter design constraints.',
                'resourceType' => self::enumCase(ResourceType::class, ['LINK', 'URL'], ['link', 'url']),
                'externalUrl' => 'https://example.test/lt8711-datasheet',
                'originalFilename' => null,
                'storagePath' => null,
                'mimeType' => null,
                'sizeBytes' => null,
                'checksumSha256' => null,
                'createdAt' => new \DateTimeImmutable('-20 days'),
                'updatedAt' => new \DateTimeImmutable('-2 days'),
                'archivedAt' => null,
                'deletedAt' => null,
            ]),
            'studycontent-api-spec' => ResourceFactory::createOne([
                'user' => $users['emma'],
                'subject' => $subjects['web-technology'],
                'title' => 'StudyContent API scratch spec',
                'description' => 'Local design notes for notes/resources/tags API endpoints.',
                'resourceType' => self::enumCase(ResourceType::class, ['FILE', 'UPLOAD'], ['file', 'upload']),
                'externalUrl' => null,
                'originalFilename' => 'studycontent-api-spec.md',
                'storagePath' => 'fixtures/demo/studycontent-api-spec.md',
                'mimeType' => 'text/markdown',
                'sizeBytes' => 18000,
                'checksumSha256' => hash('sha256', 'studycontent-api-spec.md'),
                'createdAt' => new \DateTimeImmutable('-6 days'),
                'updatedAt' => new \DateTimeImmutable('-1 day'),
                'archivedAt' => null,
                'deletedAt' => null,
            ]),
        ];

        foreach ($resources as $key => $resource) {
            $this->addState("demo-resource-{$key}", $resource);
            $this->addToPool('resources-demo', [$resource]);
        }

        ResourceTagFactory::createOne([
            'resource' => $resources['lt8711-datasheet'],
            'tag' => $tags['lab'],
            'createdAt' => new \DateTimeImmutable('-20 days'),
        ]);

        ResourceTagFactory::createOne([
            'resource' => $resources['studycontent-api-spec'],
            'tag' => $tags['backend'],
            'createdAt' => new \DateTimeImmutable('-6 days'),
        ]);

        $session1Started = new \DateTimeImmutable('-2 days');

        $session1 = SessionFactory::createOne([
            'user' => $users['emma'],
            'status' => self::enumCase(SessionStatus::class, ['COMPLETED', 'DONE'], ['completed', 'done']),
            'sessionType' => self::enumCase(SessionType::class, ['FOCUS', 'POMODORO'], ['focus', 'pomodoro']),
            'startedAt' => $session1Started,
            'endedAt' => $session1Started->modify('+95 minutes'),
            'plannedMinutes' => 100,
            'actualFocusMinutes' => 88,
            'breakMinutes' => 10,
            'distractionCount' => 2,
            'sessionNote' => 'Good progress on measurement section and report structure.',
            'createdAt' => $session1Started,
            'updatedAt' => $session1Started->modify('+95 minutes'),
        ]);

        $session2Started = new \DateTimeImmutable('-1 day');

        $session2 = SessionFactory::createOne([
            'user' => $users['emma'],
            'status' => self::enumCase(SessionStatus::class, ['COMPLETED', 'DONE'], ['completed', 'done']),
            'sessionType' => self::enumCase(SessionType::class, ['FOCUS', 'POMODORO'], ['focus', 'pomodoro']),
            'startedAt' => $session2Started,
            'endedAt' => $session2Started->modify('+55 minutes'),
            'plannedMinutes' => 50,
            'actualFocusMinutes' => 48,
            'breakMinutes' => 10,
            'distractionCount' => 0,
            'sessionNote' => 'Backend endpoint design was clarified.',
            'createdAt' => $session2Started,
            'updatedAt' => $session2Started->modify('+55 minutes'),
        ]);

        $this->addState('demo-session-power-electronics', $session1);
        $this->addState('demo-session-web-technology', $session2);

        SessionEntryFactory::createOne([
            'session' => $session1,
            'subject' => $subjects['power-electronics'],
            'task' => $tasks['zeta-report'],
            'note' => $notes['zeta-measurements'],
            'plannedMinutes' => 50,
            'actualFocusMinutes' => 44,
            'entryStartedAt' => $session1Started,
            'entryEndedAt' => $session1Started->modify('+44 minutes'),
            'subjectNameSnapshot' => 'Power Electronics',
            'taskTitleSnapshot' => 'Finish zeta converter measurement section',
            'noteTitleSnapshot' => 'Zeta converter measurement notes',
            'createdAt' => $session1Started,
            'updatedAt' => $session1Started->modify('+44 minutes'),
        ]);

        SessionEntryFactory::createOne([
            'session' => $session1,
            'subject' => $subjects['power-electronics'],
            'task' => $tasks['zeta-report'],
            'note' => $notes['zeta-measurements'],
            'plannedMinutes' => 50,
            'actualFocusMinutes' => 44,
            'entryStartedAt' => $session1Started->modify('+50 minutes'),
            'entryEndedAt' => $session1Started->modify('+94 minutes'),
            'subjectNameSnapshot' => 'Power Electronics',
            'taskTitleSnapshot' => 'Finish zeta converter measurement section',
            'noteTitleSnapshot' => 'Zeta converter measurement notes',
            'createdAt' => $session1Started->modify('+50 minutes'),
            'updatedAt' => $session1Started->modify('+94 minutes'),
        ]);

        SessionEntryFactory::createOne([
            'session' => $session2,
            'subject' => $subjects['web-technology'],
            'task' => $tasks['studycontent-upload'],
            'note' => $notes['symfony-plan'],
            'plannedMinutes' => 50,
            'actualFocusMinutes' => 48,
            'entryStartedAt' => $session2Started,
            'entryEndedAt' => $session2Started->modify('+48 minutes'),
            'subjectNameSnapshot' => 'Software & Web Technology',
            'taskTitleSnapshot' => 'Implement StudyContent resource upload flow',
            'noteTitleSnapshot' => 'StudyContent implementation plan',
            'createdAt' => $session2Started,
            'updatedAt' => $session2Started->modify('+48 minutes'),
        ]);
    }
}
