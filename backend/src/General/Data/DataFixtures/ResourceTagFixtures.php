<?php

namespace App\General\Data\DataFixtures;

use App\Feature\Resource\Entity\Resource;
use App\Feature\Tag\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ResourceTagFixtures extends Fixture implements FixtureGroupInterface
{
    /**
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['dev', 'demo'];
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            Resource::class,
            Tag::class,
        ];
    }
}
