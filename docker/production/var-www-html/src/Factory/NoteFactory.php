<?php

namespace App\Factory;

use App\Entity\Note;
use App\Enum\NoteSourceType;
use App\Factory\Traits\SubjectFactoryTrait;
use App\Factory\Traits\TaskFactoryTrait;
use App\Factory\Traits\UserFactoryTrait;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Note>
 */
final class NoteFactory extends PersistentObjectFactory
{
    use UserFactoryTrait;
    use SubjectFactoryTrait;
    use TaskFactoryTrait;

    #[\Override]
    public static function class(): string
    {
        return Note::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'title' => ucfirst(self::faker()->words(3, true)),
            'content' => self::faker()->paragraphs(5, true),
            'sourceType' => NoteSourceType::default(),
        ];
    }

    public function basic(int $number): static
    {
        return $this->with([
            'title' => "note{$number}",
            'content' => self::faker()->paragraphs(5, true),
        ]);
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

}
