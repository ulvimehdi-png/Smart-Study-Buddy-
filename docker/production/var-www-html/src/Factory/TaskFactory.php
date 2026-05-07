<?php

namespace App\Factory;

use App\Entity\Subject;
use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskPriority;
use App\Enum\TaskStatus;
use App\Factory\Traits\SubjectFactoryTrait;
use App\Factory\Traits\UserFactoryTrait;
use App\Repository\TaskRepository;
use phpDocumentor\Reflection\Types\Integer;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;

/**
 * @extends PersistentObjectFactory<Task>
 */
final class TaskFactory extends PersistentObjectFactory
{
    use UserFactoryTrait;
    use SubjectFactoryTrait;

    #[\Override]
    public static function class(): string
    {
        return Task::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'title' => ucfirst(self::faker()->words(3, true)),
            'status' => TaskStatus::default(),
            'priority' => TaskPriority::default(),
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (Task $task): void {
                if ($task->getStatus() !== TaskStatus::DONE) {
                    $task->setCompletedAt(null);
                }
            });
    }

    public function basic(int $number): static
    {
        return $this->with([
            'title' => "task{$number}",
        ]);
    }
}
