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

#[AsFixture(name: 'baseStory', groups: ['base'])]
final class BaseStory extends Story
{
    private const string POOL_BASIC_USERS = 'users-basic';
    private const string POOL_BASIC_SUBJECTS = 'subjects-basic';
    private const string POOL_BASIC_TASKS = 'tasks-basic';
    private const string POOL_BASIC_NOTES = 'notes-basic';

    private const int BASIC_USERS = 5;
    private const int BASIC_SUBJECTS = 20;
    private const int BASIC_TASKS = 20;
    private const int BASIC_NOTES = 20;

    public function build(): void
    {
        $this->createAdmin();

        $users = $this->createUsers();
        $subjects = $this->createSubjects($users);
        $tasks = $this->createTasks($users, $subjects);

        $this->createNotes($users, $subjects, $tasks);
    }

    private function createAdmin(): void
    {
        $admin = UserFactory::new()
            ->basic(0)
            ->isAdmin()
            ->create();

        $this->addState('admin', $admin);
    }

    /**
     * @return array<int, User>
     */
    private function createUsers(): array
    {
        $users = [];

        foreach (range(1, self::BASIC_USERS) as $i) {
            $users[$i] = UserFactory::new()
                ->basic($i)
                ->create();
        }

        $this->registerCollection(
            self::POOL_BASIC_USERS,
            'user',
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
        $subjects = [];

        foreach (range(1, self::BASIC_SUBJECTS) as $i) {
            $userIndex = (($i - 1) % count($users)) + 1;

            $subjects[$i] = SubjectFactory::new()
                ->basic($i)
                ->forUser($users[$userIndex])
                ->create();
        }

        $this->registerCollection(
            self::POOL_BASIC_SUBJECTS,
            'subject',
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
        $tasks = [];

        foreach (range(1, self::BASIC_TASKS) as $i) {
            $userIndex = (($i - 1) % count($users)) + 1;

            $tasks[$i] = TaskFactory::new()
                ->basic($i)
                ->forUser($users[$userIndex])
                ->forSubject($subjects[$i])
                ->create();
        }

        $this->registerCollection(
            self::POOL_BASIC_TASKS,
            'task',
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
        $notes = [];

        foreach (range(1, self::BASIC_NOTES) as $i) {
            $userIndex = (($i - 1) % count($users)) + 1;

            $notes[$i] = NoteFactory::new()
                ->basic($i)
                ->forUser($users[$userIndex])
                ->forSubject($subjects[$i])
                ->forTask($tasks[$i])
                ->create();
        }

        $this->registerCollection(
            self::POOL_BASIC_NOTES,
            'note',
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
