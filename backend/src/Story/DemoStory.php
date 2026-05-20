<?php

namespace App\Story;

use App\Entity\Subject;
use App\Entity\Task;
use App\Entity\User;
use App\Factory\NoteFactory;
use App\Factory\SubjectFactory;
use App\Factory\TaskFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'demoStory', groups: ['demo'])]
final class DemoStory extends Story
{
    private const string POOL_PRESENTATION_USERS = 'presentation-users';
    private const string POOL_PRESENTATION_SUBJECTS = 'presentation-subjects';
    private const string POOL_PRESENTATION_TASKS = 'presentation-tasks';
    private const string POOL_PRESENTATION_NOTES = 'presentation-notes';

    private const int PRESENTATION_USERS = 3;

    public function build(): void
    {
        $users = $this->createUsers();
        $subjects = $this->createSubjects($users);
        $tasks = $this->createTasks($users, $subjects);

        $this->createNotes($users, $subjects, $tasks);
    }

    /**
     * @return array<int, User>
     */
    private function createUsers(): array
    {
        $users = [];

        $users[1] = UserFactory::new()
            ->basic(100)
            ->create([
                'username' => 'demo',
                'email' => 'demo@studybuddy.test',
                'displayName' => 'Demo Student',
                'points' => 1860,
                'currentStreak' => 9,
                'longestStreak' => 21,
                'lastLoginAt' => new \DateTimeImmutable('-2 hours'),
            ]);

        $users[2] = UserFactory::new()
            ->basic(101)
            ->create([
                'username' => 'alex',
                'email' => 'alex@studybuddy.test',
                'displayName' => 'Alex Engineer',
                'points' => 920,
                'currentStreak' => 4,
                'longestStreak' => 11,
                'lastLoginAt' => new \DateTimeImmutable('-1 day'),
            ]);

        $users[3] = UserFactory::new()
            ->basic(102)
            ->create([
                'username' => 'sam',
                'email' => 'sam@studybuddy.test',
                'displayName' => 'Sam Researcher',
                'points' => 540,
                'currentStreak' => 2,
                'longestStreak' => 8,
                'lastLoginAt' => new \DateTimeImmutable('-3 days'),
            ]);

        $this->registerCollection(
            self::POOL_PRESENTATION_USERS,
            'presentationUser',
            $users
        );

        return $users;
    }

    /**
     * @param array<int, User> $users
     *
     * @return array<int, Subject>
     */
    private function createSubjects(array $users): array
    {
        $rows = [
            1 => [1, 'WEBTECH', 'Web Technology', 'Symfony, React, deployment, CI/CD, and realtime timers.'],
            2 => [1, 'ML', 'Machine Learning', 'Dry Bean classification, evaluation, macro-F1, and model comparison.'],
            3 => [1, 'ELEC', 'Power Electronics', 'LTspice simulations, ripple analysis, and buck-boost design.'],
            4 => [1, 'EMBED', 'Embedded Systems', 'Microcontrollers, UART protocols, and scheduling.'],
            5 => [1, 'RND', 'Research Methods', 'Presentation structure, references, and report quality.'],

            6 => [2, 'NET', 'Computer Networks', 'VLANs, reverse proxies, DNS, and network segmentation.'],
            7 => [2, 'DB', 'Database Systems', 'Doctrine mapping, relations, migrations, and fixtures.'],

            8 => [3, 'UX', 'User Experience', 'Dashboard clarity, onboarding, and visual consistency.'],
            9 => [3, 'SEC', 'Application Security', 'Authentication, authorization, upload handling, and API logout.'],
        ];

        $subjects = [];

        foreach ($rows as $i => [$userIndex, $code, $name, $description]) {
            $subjects[$i] = SubjectFactory::new()
                ->basic($i + 100)
                ->forUser($users[$userIndex])
                ->create([
                    'code' => $code,
                    'name' => $name,
                    'description' => $description,
                ]);
        }

        $this->registerCollection(
            self::POOL_PRESENTATION_SUBJECTS,
            'presentationSubject',
            $subjects
        );

        return $subjects;
    }

    /**
     * @param array<int, User> $users
     * @param array<int, Subject> $subjects
     *
     * @return array<int, Task>
     */
    private function createTasks(array $users, array $subjects): array
    {
        $rows = [
            1 => [1, 1, 'Finish Symfony security tests', 'Cover register, profile update, password update, and image upload branches.', '+1 day'],
            2 => [1, 1, 'Prepare admin dashboard demo', 'Show Mercure health, writable upload directory, latest logs, and registered users.', '+2 days'],
            3 => [1, 1, 'Finalize CI coverage deployment', 'Generate PHPUnit coverage and publish it under /upload/coverage.', '+2 days'],
            4 => [1, 2, 'Export Dry Bean confusion matrix', 'Prepare model comparison figure for the presentation.', '-1 day'],
            5 => [1, 2, 'Write macro-F1 explanation', 'Explain why macro-F1 is relevant for multiclass imbalance.', '+3 days'],
            6 => [1, 3, 'Capture LTspice ripple plots', 'Collect screenshots for input/output ripple behavior.', '-2 days'],
            7 => [1, 3, 'Summarize buck-boost efficiency', 'Prepare graph and short interpretation for report discussion.', '+4 days'],
            8 => [1, 4, 'Document UART frame format', 'Prepare protocol diagram and validation notes.', '+5 days'],
            9 => [1, 5, 'Finalize presentation storyline', 'Problem, architecture, implementation, demo, evaluation, reflection.', '+1 day'],

            10 => [2, 6, 'Prepare VLAN diagram', 'Show current and target homelab network segmentation.', '+6 days'],
            11 => [2, 7, 'Review Doctrine relations', 'Verify nullable and non-nullable ownership before fixture expansion.', '+2 days'],

            12 => [3, 8, 'Polish dashboard empty states', 'Improve first-login experience and presentation readability.', '+4 days'],
            13 => [3, 9, 'Check upload execution blocking', 'Verify Apache refuses PHP execution inside /upload.', '+1 day'],
        ];

        $tasks = [];

        foreach ($rows as $i => [$userIndex, $subjectIndex, $title, $description, $due]) {
            $tasks[$i] = TaskFactory::new()
                ->basic($i + 100)
                ->forUser($users[$userIndex])
                ->forSubject($subjects[$subjectIndex])
                ->create([
                    'title' => $title,
                    'description' => $description,
                    'dueAt' => new \DateTimeImmutable($due),
                ]);
        }

        $this->registerCollection(
            self::POOL_PRESENTATION_TASKS,
            'presentationTask',
            $tasks
        );

        return $tasks;
    }

    /**
     * @param array<int, User> $users
     * @param array<int, Subject> $subjects
     * @param array<int, Task> $tasks
     */
    private function createNotes(
        array $users,
        array $subjects,
        array $tasks,
    ): void
    {
        $rows = [
            1 => [1, 1, 1, 'Symfony security notes', 'Logout must return JSON or 204 for API usage; redirects are wrong for XHR.'],
            2 => [1, 1, 2, 'Deployment routing notes', '/api goes through Symfony, /upload is static Apache, React handles client-side routing.'],
            3 => [1, 1, 3, 'Coverage deployment notes', 'Coverage is generated in the test image and deployed as static files under /upload/coverage.'],
            4 => [1, 2, 4, 'Dry Bean evaluation summary', 'Decision trees are interpretable; MLP improves predictive performance after scaling.'],
            5 => [1, 2, 5, 'Macro-F1 note', 'Macro-F1 gives each class equal weight and is useful for comparing per-class behavior.'],
            6 => [1, 3, 6, 'Power electronics simulation note', 'Ripple and transient behavior are central for the 12 V universal supply discussion.'],
            7 => [1, 3, 7, 'Efficiency plot interpretation', 'Efficiency should be discussed across input voltage and load ranges.'],
            8 => [1, 4, 8, 'Embedded protocol checklist', 'Keep frame format deterministic and include checksum/error handling.'],
            9 => [1, 5, 9, 'Presentation structure', 'Start from problem context, then architecture, demo, metrics, and reflection.'],

            10 => [2, 6, 10, 'Network segmentation note', 'Use diagrams to explain trust zones and traffic flows.'],
            11 => [2, 7, 11, 'Doctrine mapping note', 'Required user ownership must be respected in fixtures and tests.'],

            12 => [3, 8, 12, 'UX dashboard note', 'Presentation screenshots need populated cards, charts, and recent activity.'],
            13 => [3, 9, 13, 'Upload security note', 'Uploads should be outside public build assets and PHP execution must be disabled.'],
        ];

        $notes = [];

        foreach ($rows as $i => [$userIndex, $subjectIndex, $taskIndex, $title, $content]) {
            $notes[$i] = NoteFactory::new()
                ->basic($i + 100)
                ->forUser($users[$userIndex])
                ->forSubject($subjects[$subjectIndex])
                ->forTask($tasks[$taskIndex])
                ->create([
                    'title' => $title,
                    'content' => $content,
                ]);
        }

        $this->registerCollection(
            self::POOL_PRESENTATION_NOTES,
            'presentationNote',
            $notes
        );
    }

    /**
     * @param array<int, object> $entities
     */
    private function registerCollection(
        string $pool,
        string $prefix,
        array  $entities,
    ): void
    {
        foreach ($entities as $i => $entity) {
            $this->addState("{$prefix}{$i}", $entity);
        }

        $this->addToPool($pool, $entities);
    }
}
