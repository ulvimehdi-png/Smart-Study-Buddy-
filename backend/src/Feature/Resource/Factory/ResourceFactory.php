<?php

namespace App\Feature\Resource\Factory;

use App\Feature\Resource\Entity\Resource;
use App\Feature\Resource\Enum\ResourceType;
use Override;
use phpDocumentor\Reflection\Types\Integer;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<resource>
 */
final class ResourceFactory extends PersistentObjectFactory
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
        return Resource::class;
    }

    public function link(?string $url = null): static
    {
        return $this->with([
            'resource_type' => ResourceType::LINK,
            'external_url' => $url ?? self::faker()->url(),
        ]);
    }

    public function file(
        ?string  $originalFilename = null,
        ?string  $storagePath = null,
        ?string  $mimeType = null,
        ?Integer $sizeBytes = null,
        ?string  $checksumSha256 = null
    ): static
    {
        return $this->with([
            'resourceType' => ResourceType::FILE,
            'originalFilename' => $originalFilename ?? self::faker()->randomElement(['summary.pdf', 'slides.pdf', 'notes.docx']),
            'storagePath' => $storagePath ?? '/fixtures/resources/' . self::faker()->uuid() . '.bin',
            'mimeType' => $mimeType ?? self::faker()->randomElement(['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']),
            'sizeBytes' => $sizeBytes ?? self::faker()->numberBetween(50_000, 2_000_000),
            'checksumSha256' => $checksumSha256 ?? hash('sha256', self::faker()->uuid()),
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
            'resourceType' => ResourceType::LINK,
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
