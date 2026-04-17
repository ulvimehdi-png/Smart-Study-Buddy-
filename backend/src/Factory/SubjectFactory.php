<?php

namespace App\Factory;

use App\Entity\Subject;
use App\Repository\SubjectRepository;
use phpDocumentor\Reflection\Types\Integer;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;

/**
 * @extends PersistentObjectFactory<Subject>
 */
final class SubjectFactory extends PersistentObjectFactory
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

    #[\Override]
    public static function class(): string
    {
        return Subject::class;
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
            'name' => self::faker()->words(2,true),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }

    /*
     *
     * FACTORY STATES
     *
     */

    public function withAllOptions(
        string $name,
        ?string $code = null,
        ?string $description = null,
        ?string $colorHex = null,
        ?Integer $targetTotalMinutes = null,
        ?Integer $targetWeeklyMinutes = null,
    ): static
    {
        return $this->with([
            'name' => $name,
            'code' => $code ?? self::faker()->word,
            'description' => $description ?? self::faker()->paragraphs(),
            'colorHex' => $colorHex ?? self::faker()->hexColor(),
            // 1 credit = 30hr, set max 6 credits
            'targetTotalMinutes' => $targetTotalMinutes ?? self::faker()->numberBetween(30*60, 6*30*60),
            // 13 weeks per semester
            'targetWeeklyMinutes' => $targetWeeklyMinutes ?? self::faker()->numberBetween(30*60/13, 6*30*60/13),
        ]);
    }

    public function archived(?\DateTimeImmutable $archivedDate = null): static
    {
        return $this->with([
            'archivedAt' => $archivedDate ?? new \DateTimeImmutable(),
        ]);
    }

    /**
     * @return ProxyRepositoryDecorator<Subject, SubjectRepository>
     */
    public static function repository(): ProxyRepositoryDecorator
    {
        return parent::repository();
    }
}
