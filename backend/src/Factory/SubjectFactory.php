<?php

namespace App\Factory;

use App\Entity\Subject;
use App\Factory\Traits\UserFactoryTrait;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Subject>
 */
final class SubjectFactory extends PersistentObjectFactory
{
    use UserFactoryTrait;

    #[\Override]
    public static function class(): string
    {
        return Subject::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->words(2, true),
        ];
    }

    public function basic(int $number): static
    {
        return $this->with([
            'code' => sprintf('SUB%02d', $number),
            'name' => "subject{$number}",
        ]);
    }
}
