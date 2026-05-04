<?php

namespace App\Factory;

use App\Entity\Subject;
use App\Repository\SubjectRepository;
use phpDocumentor\Reflection\Types\Integer;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Persistence\RepositoryDecorator;

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

    public function withCode(?string $code = null): static
    {
        return $this->with([
            'code' => $code ?? self::faker()->word,
        ]);
    }

    public function withDescription(?string $description = null,): static
    {
        return $this->with([
            'description' => $description ?? self::faker()->paragraphs(),
        ]);
    }

    public function withColorHex(): static
    {
        return $this->with([
            'colorHex' => $colorHex ?? self::faker()->hexColor(),
        ]);
    }

    public function withTargetMinutes(
        ?Integer $targetTotalMinutes = null,
        ?Integer $targetWeeklyMinutes = null,
    ): static
    {
        $minutesPerCredit = self::faker()->numberBetween(20,40) * 60;
        $subjectCredit = self::faker()->numberBetween(3,7);
        $subjectWeeks = self::faker()->numberBetween(4,52);

        $targetTotalMinutes = $minutesPerCredit*$subjectCredit;
        $targetWeeklyMinutes = intdiv($targetTotalMinutes, $subjectWeeks);

        return $this->with([
            'targetTotalMinutes' => $targetTotalMinutes,
            'targetWeeklyMinutes' => $targetWeeklyMinutes,
        ]);
    }

    public function archived(?\DateTimeImmutable $archivedDate = null): static
    {
        return $this->with([
            'archivedAt' => $archivedDate ?? new \DateTimeImmutable(),
        ]);
    }

    /**
     * @return RepositoryDecorator<Subject, SubjectRepository>
     */
    public static function repository(): RepositoryDecorator
    {
        return parent::repository();
    }
}
