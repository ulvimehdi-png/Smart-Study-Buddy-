<?php

namespace App\Feature\Note\Factory;

use App\Feature\Note\Entity\Note;
use App\Feature\Note\Enum\NoteSourceType;
use Override;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Note>
 */
final class NoteFactory extends PersistentObjectFactory
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
        return Note::class;
    }

    public function link(?string $url = null): static
    {
        return $this->with([
            'sourceType' => NoteSourceType::LINK,
            'externalUrl' => $url ?? self::faker()->url(),
        ]);
    }

    public function standalone(): static
    {
        return $this->with([
            'subject' => null,
            'task' => null,
        ]);
    }

    /*
     *
     * FACTORY STATES
     *
     */

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[Override]
    protected function defaults(): array|callable
    {
        return [
            'title' => ucfirst(self::faker()->words(3, true)),
            'content' => self::faker()->paragraphs(5, true),
            'sourceType' => NoteSourceType::default(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[Override]
    protected function initialize(): static
    {
        return $this;
    }
}
