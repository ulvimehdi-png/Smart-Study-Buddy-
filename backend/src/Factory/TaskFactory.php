<?php

namespace App\Factory;

use App\Entity\Task;
use App\Enum\TaskPriority;
use App\Enum\TaskStatus;
use App\Repository\TaskRepository;
use phpDocumentor\Reflection\Types\Integer;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Persistence\RepositoryDecorator;

/**
 * @extends PersistentObjectFactory<Task>
 */
final class TaskFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return Task::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'title' => ucfirst(self::faker()->words(4, true)),
            'status' => TaskStatus::default(),
            'priority' => TaskPriority::default(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
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

    /*
     *
     * FACTORY STATES
     *
     */

    public function withStatus(TaskStatus $status): static
    {
        return $this->with([
            'status' => $status,
            'completedAt' => $status === TaskStatus::DONE ? new \DateTimeImmutable() : null,
        ]);
    }

    public function withDescription(?string $description = null,): static
    {
        return $this->with([
            'description' => $description ?? self::faker()->paragraphs(),
        ]);
    }

    public function withEstimatedMinutes(?Integer $estimatedMinutes = null): static
    {
        return $this->with([
            //mutiple of 15
            'estimatedMinutes' => $estimatedMinutes ?? round(self::faker()->numberBetween(10,600)/15)*15,
        ]);
    }

    public function withDueAt(?\DateTimeImmutable $dueAt = null): static
    {
        return $this->with([
            'dueAt' => $dueAt ?? self::faker()->dateTimeBetween('now', '+30 days'),
        ]);
    }

    public function withScheduledFor(?\DateTimeImmutable $scheduledFor = null): static
    {
        return $this->with([
            'scheduledFor' => $scheduledFor ?? new \DateTimeImmutable(),
        ]);
    }

    public function archived(?\DateTimeImmutable $archivedDate = null): static
    {
        return $this->with([
            'archivedAt' => $archivedDate ?? new \DateTimeImmutable(),
        ]);
    }

    /**
     * @return RepositoryDecorator<Task, TaskRepository>
     */
    public static function repository(): RepositoryDecorator
    {
        return parent::repository();
    }
}
