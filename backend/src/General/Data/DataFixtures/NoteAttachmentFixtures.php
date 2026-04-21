<?php

namespace App\General\Data\DataFixtures;

use App\Feature\Note\Entity\Note;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class NoteAttachmentFixtures extends Fixture implements FixtureGroupInterface
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
            Note::class,
        ];
    }
}
