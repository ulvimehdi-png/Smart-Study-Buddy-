<?php

namespace App\Factory;

use App\Entity\Settings;
use App\Enum\SessionType;
use App\Enum\WeekStartDay;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Settings>
 */
final class SettingsFactory extends PersistentObjectFactory
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
        return Settings::class;
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
            'timezone' => 'Europe/Brussels',
            'weekStartDay' => WeekStartDay::default(),
            'defaultSessionType' => SessionType::default(),
            'defaultSessionMinutes' => 50,
            'defaultBreakMinutes' => 10,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Settings $settings): void {})
        ;
    }
}
