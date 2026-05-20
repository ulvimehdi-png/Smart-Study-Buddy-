<?php

namespace App\Story;

use App\Entity\Note;
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
    private const string POOL_DEMO_USERS = 'demo-users';
    private const string POOL_DEMO_SUBJECTS = 'demo-subjects';
    private const string POOL_DEMO_TASKS = 'demo-tasks';
    private const string POOL_DEMO_NOTES = 'demo-notes';

    private const int TASKS_PER_SUBJECT = 6;
    private const int NOTES_PER_SUBJECT = 4;

    public function build(): void
    {
        $users = $this->createUsers();
        $subjects = $this->createSubjects($users);
        $tasks = $this->createTasks($subjects);

        $this->createNotes($subjects, $tasks);
    }

    /**
     * @return array<int, User>
     */
    private function createUsers(): array
    {
        $users = [];

        $users[1] = UserFactory::new()
            ->basic(200)
            ->create([
                'username' => 'demo',
                'email' => 'demo@studybuddy.test',
                'displayName' => 'Demo Student',
                'points' => 2450,
                'currentStreak' => 12,
                'longestStreak' => 28,
                'lastLoginAt' => new \DateTimeImmutable('-1 hour'),
            ]);

        $users[2] = UserFactory::new()
            ->basic(201)
            ->isAdmin()
            ->create([
                'username' => 'admin',
                'email' => 'admin@studybuddy.test',
                'displayName' => 'Admin Developer',
                'points' => 5200,
                'currentStreak' => 18,
                'longestStreak' => 42,
                'lastLoginAt' => new \DateTimeImmutable('-20 minutes'),
            ]);

        $users[3] = UserFactory::new()
            ->basic(202)
            ->create([
                'username' => 'lisa',
                'email' => 'lisa@studybuddy.test',
                'displayName' => 'Lisa Analyst',
                'points' => 1320,
                'currentStreak' => 5,
                'longestStreak' => 13,
                'lastLoginAt' => new \DateTimeImmutable('-1 day'),
            ]);

        $users[4] = UserFactory::new()
            ->basic(203)
            ->create([
                'username' => 'noah',
                'email' => 'noah@studybuddy.test',
                'displayName' => 'Noah Designer',
                'points' => 980,
                'currentStreak' => 3,
                'longestStreak' => 9,
                'lastLoginAt' => new \DateTimeImmutable('-2 days'),
            ]);

        $users[5] = UserFactory::new()
            ->basic(204)
            ->create([
                'username' => 'maya',
                'email' => 'maya@studybuddy.test',
                'displayName' => 'Maya Researcher',
                'points' => 1760,
                'currentStreak' => 7,
                'longestStreak' => 19,
                'lastLoginAt' => new \DateTimeImmutable('-6 hours'),
            ]);

        $this->registerCollection(
            self::POOL_DEMO_USERS,
            'demoUser',
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
            [1, 'SEWT', 'Software Engineering', 'Requirements, architecture, testing, CI/CD, maintainability, and deployment workflows.'],
            [1, 'WEB', 'Web Application Development', 'React, Symfony, API integration, routing, authentication, and frontend/backend boundaries.'],
            [1, 'DBS', 'Database Systems', 'Relational modeling, Doctrine ORM, migrations, indexes, and data integrity.'],
            [1, 'SEC', 'Application Security', 'Authentication, authorization, upload security, CORS, session handling, and secure deployment.'],
            [1, 'UXD', 'User Experience Design', 'Dashboard usability, user flows, accessibility, layout quality, and visual consistency.'],

            [2, 'ADMIN', 'Developer Operations', 'Admin monitoring, logs, coverage reports, deployment checks, and operational diagnostics.'],
            [2, 'CI', 'Continuous Integration', 'Automated tests, build artifacts, deployment scripts, PHPUnit coverage, and GitLab pipelines.'],
            [2, 'QA', 'Quality Assurance', 'Feature verification, regression testing, smoke tests, and release readiness.'],
            [2, 'OPS', 'Production Operations', 'Apache routing, environment files, migrations, cache warmup, and static upload handling.'],
            [2, 'OBS', 'Observability', 'Logs, health checks, status pages, dashboards, and troubleshooting workflow.'],

            [3, 'STAT', 'Applied Statistics', 'Descriptive statistics, distributions, hypothesis testing, confidence intervals, and visual analysis.'],
            [3, 'ML', 'Machine Learning', 'Classification, model evaluation, validation splits, confusion matrices, and performance metrics.'],
            [3, 'BI', 'Business Intelligence', 'Dashboards, KPIs, reporting, data summaries, and decision-support metrics.'],
            [3, 'DATA', 'Data Engineering', 'Data cleaning, feature preparation, import/export flows, and reproducible pipelines.'],
            [3, 'RISK', 'Risk Analysis', 'Risk identification, mitigation planning, scoring, and prioritization.'],

            [4, 'MOB', 'Mobile Development', 'Mobile UI patterns, offline behavior, notifications, API usage, and responsive design.'],
            [4, 'HCI', 'Human-Computer Interaction', 'Interaction design, user feedback loops, cognitive load, and usability validation.'],
            [4, 'PM', 'Project Management', 'Planning, milestones, task breakdowns, communication, and scope control.'],
            [4, 'DES', 'Product Design', 'Problem framing, prototyping, design systems, and iteration based on feedback.'],
            [4, 'COMM', 'Technical Communication', 'Presentation structure, demo scripts, documentation, and stakeholder explanation.'],

            [5, 'ALG', 'Algorithms', 'Complexity, graph traversal, sorting, dynamic programming, and problem-solving patterns.'],
            [5, 'OS', 'Operating Systems', 'Processes, scheduling, memory, filesystems, concurrency, and system-level reasoning.'],
            [5, 'NET', 'Computer Networks', 'HTTP, DNS, routing, firewalls, reverse proxies, ports, and traffic flows.'],
            [5, 'CLOUD', 'Cloud Infrastructure', 'Containers, services, environment isolation, deployment targets, and runtime configuration.'],
            [5, 'AIETH', 'AI Ethics', 'Bias, transparency, accountability, privacy, and responsible model usage.'],
        ];

        $subjects = [];

        foreach ($rows as $i => [$userIndex, $code, $name, $description]) {
            $subjects[$i + 1] = SubjectFactory::new()
                ->basic($i + 300)
                ->forUser($users[$userIndex])
                ->create([
                    'code' => $code,
                    'name' => $name,
                    'description' => $description,
                ]);
        }

        $this->registerCollection(
            self::POOL_DEMO_SUBJECTS,
            'demoSubject',
            $subjects
        );

        return $subjects;
    }

    /**
     * @param array<int, Subject> $subjects
     *
     * @return array<int, Task>
     */
    private function createTasks(array $subjects): array
    {
        $templates = [
            ['Review lecture material', 'Read the core theory and summarize the most important concepts.'],
            ['Prepare practical exercise', 'Work through exercises and document blockers or unclear points.'],
            ['Create presentation slide', 'Prepare one clean visual slide with key takeaways and examples.'],
            ['Write implementation notes', 'Document design choices, constraints, and trade-offs.'],
            ['Validate feature behavior', 'Test the expected behavior and record any remaining issues.'],
            ['Prepare demo scenario', 'Create a small realistic scenario that can be shown during the presentation.'],
        ];

        $tasks = [];
        $i = 1;

        foreach ($subjects as $subject) {
            $user = $subject->getUser();

            foreach ($templates as $j => [$title, $description]) {
                $dueOffset = (($i % 17) - 5);
                $due = $dueOffset >= 0 ? "+{$dueOffset} days" : "{$dueOffset} days";

                $tasks[$i] = TaskFactory::new()
                    ->basic($i + 300)
                    ->forUser($user)
                    ->forSubject($subject)
                    ->create([
                        'title' => "{$title}: {$subject->getName()}",
                        'description' => "{$description} Subject: {$subject->getName()}.",
                        'dueAt' => new \DateTimeImmutable($due),
                    ]);

                ++$i;
            }
        }

        $this->registerCollection(
            self::POOL_DEMO_TASKS,
            'demoTask',
            $tasks
        );

        return $tasks;
    }

    /**
     * @param array<int, Subject> $subjects
     * @param array<int, Task> $tasks
     */
    private function createNotes(array $subjects, array $tasks): void
    {
        $templates = [
            ['Concept summary', 'Key definitions, assumptions, diagrams, and common pitfalls collected for quick review.'],
            ['Practical findings', 'Observed behavior, important edge cases, and implementation notes from hands-on work.'],
            ['Presentation notes', 'Short explanation intended for a demo slide or spoken presentation segment.'],
            ['Revision checklist', 'Checklist of items to verify before the final evaluation or live demonstration.'],
        ];

        $notes = [];
        $i = 1;

        foreach ($subjects as $subjectIndex => $subject) {
            $user = $subject->getUser();

            for ($j = 0; $j < self::NOTES_PER_SUBJECT; ++$j) {
                [$title, $content] = $templates[$j % count($templates)];

                $taskIndex = (($subjectIndex - 1) * self::TASKS_PER_SUBJECT) + (($j % self::TASKS_PER_SUBJECT) + 1);
                $task = $tasks[$taskIndex] ?? null;

                $notes[$i] = NoteFactory::new()
                    ->basic($i + 300)
                    ->forUser($user)
                    ->forSubject($subject)
                    ->forTask($task)
                    ->create([
                        'title' => "{$title}: {$subject->getName()}",
                        'content' => "{$content}\n\nThis note belongs to {$subject->getName()} and is part of the demo dataset.",
                    ]);

                ++$i;
            }
        }

        $this->registerCollection(
            self::POOL_DEMO_NOTES,
            'demoNote',
            $notes
        );
    }

    /**
     * @param array<int, object> $entities
     */
    private function registerCollection(
        string $pool,
        string $prefix,
        array $entities,
    ): void {
        foreach ($entities as $i => $entity) {
            $this->addState("{$prefix}{$i}", $entity);
        }

        $this->addToPool($pool, $entities);
    }
}
