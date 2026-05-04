<?php

namespace App\Factory;

use App\Entity\Session;
use App\LiveTimerSession\Enum\SessionStatus;
use App\LiveTimerSession\Enum\SessionType;
use App\Repository\SessionRepository;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Persistence\RepositoryDecorator;

/**
 * @extends PersistentObjectFactory<Session>
 */
final class SessionFactory extends PersistentObjectFactory
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
        return Session::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        $startedAt = \DateTimeImmutable::createFromMutable(
            self::faker()->dateTimeBetween('-30 days')
        );

        return [
            'status' => SessionStatus::default(), //ACTIVE
            'sessionType' => SessionType::default(),
            'startedAt' => $startedAt,
            'breakMinutes' => 0,
            'distractionCount' => 0,
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

    public function completed(): static
    {
        return $this->with(function () {
            $focus = self::faker()->numberBetween(25, 150);
            $break = self::faker()->numberBetween(0, 30);
            $planned = $focus + $break;

            $startedAt = \DateTimeImmutable::createFromMutable(
                self::faker()->dateTimeBetween('-30 days', 'now')
            );

            return [
                'status' => SessionStatus::COMPLETED,
                'startedAt' => $startedAt,
                'endedAt' => $startedAt->modify("+{$planned} minutes"),
                'plannedMinutes' => $planned,
                'actualFocusMinutes' => $focus,
                'breakMinutes' => $break,
                'distractionCount' => self::faker()->numberBetween(0, 5),
            ];
        });
    }

    public function cancelled(): static
    {
        return $this->with([
            'status' => SessionStatus::CANCELLED,
        ]);
    }

    /**
     * @return RepositoryDecorator<Session, SessionRepository>
     */
    public static function repository(): RepositoryDecorator
    {
        return parent::repository();
    }
}
