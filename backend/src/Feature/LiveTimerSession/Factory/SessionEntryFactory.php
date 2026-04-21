<?php

namespace App\Feature\LiveTimerSession\Factory;

use App\Feature\LiveTimerSession\Entity\SessionEntry;
use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;
use Override;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<SessionEntry>
 */
final class SessionEntryFactory extends PersistentObjectFactory
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
        return SessionEntry::class;
    }

    public function timed(
        ?DateTimeImmutable $entryStartedAt = null,
        ?DateTimeImmutable $entryEndedAt = null,
    ): static
    {
        // CASE 1 - Only 1 argument provided --> invalid
        if (
            ($entryStartedAt === null && $entryEndedAt !== null) ||
            ($entryStartedAt !== null && $entryEndedAt === null)
        ) {
            throw new InvalidArgumentException(
                'Either provide both entryStartedAt and entryEndedAt, or neither.'
            );
        }

        // CASE 2 - None provided --> generate both
        if (
            ($entryStartedAt === null && $entryEndedAt === null)
        ) {
            $entryStartedAt = DateTimeImmutable::createFromMutable(
                self::faker()->dateTimeBetween('-30 days')
            );

            $entryEndedAt = $entryStartedAt->add(
                new DateInterval(
                    "PT" . self::faker()->numberBetween(10, 90) . "M"
                )
            );
        }

        // CASE 3 - Both provided --> validate data
        if ($entryStartedAt > $entryEndedAt) {
            throw new InvalidArgumentException(
                'EntryEndedAt MUST be later than entryStartedAt'
            );
        }

        return $this->with([
            'entryStartedAt' => $entryStartedAt,
            'entryEndedAt' => $entryEndedAt,
        ]);
    }

    public function forSession(Session $session): static
    {
        return $this->with([
            'session' => $session,
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
        return [];
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
