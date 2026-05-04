<?php

namespace App\Story;

use App\Factory\NoteFactory;
use App\Factory\ResourceFactory;
use App\Factory\SessionFactory;
use App\Factory\SubjectFactory;
use App\Factory\TagFactory;
use App\Factory\TaskFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'main')]
final class AppStory extends Story
{
    public function build(): void
    {
        // First create users
        UserFactory::createMany(100);

        // For subjects, tasks, etc., randomly assign to existing users
        SubjectFactory::createMany(100, function() {
            return ['user' => UserFactory::random()];
        });

        TagFactory::createMany(100, function() {
            return ['user' => UserFactory::random()];
        });

        TaskFactory::createMany(100, function() {
            return [
                'user' => UserFactory::random(), 
                'subject' => SubjectFactory::random(),
                'dueAt' => \DateTimeImmutable::createFromMutable(\Zenstruck\Foundry\faker()->dateTimeBetween('now', '+30 days'))
            ];
        });

        NoteFactory::createMany(100, function() {
            return ['user' => UserFactory::random(), 'subject' => SubjectFactory::random()];
        });

        ResourceFactory::createMany(100, function() {
            return ['user' => UserFactory::random(), 'subject' => SubjectFactory::random()];
        });

        SessionFactory::createMany(100, function() {
            return ['user' => UserFactory::random()];
        });
    }
}
