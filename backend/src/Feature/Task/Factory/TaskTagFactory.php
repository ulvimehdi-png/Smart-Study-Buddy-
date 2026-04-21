<?php

namespace App\Feature\Task\Factory;

use App\Feature\Tag\Factory\TagFactory;
use App\Feature\Task\Entity\TaskTag;
use Override;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<TaskTag>
 */
final class TaskTagFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    #[Override]
    public static function class(): string
    {
        return TaskTag::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[Override]
    protected function defaults(): array|callable
    {
        return [
            'tag' => TagFactory::new(),
            'task' => TaskFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[Override]
    protected function initialize(): static
    {
        return $this// ->afterInstantiate(function(TaskTag $taskTag): void {})
            ;
    }
}
