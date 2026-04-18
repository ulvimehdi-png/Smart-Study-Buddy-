<?php

namespace App\Story;

use App\Factory\SubjectFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'baseStory', groups: ['base'])]
final class BaseStory extends Story
{
    public function build(): void
    {
        // BaseStory follows the deterministic naming conventions started in MinimalStory
        //  e.g. (user1, user2, user3, ...)
        MinimalStory::getPool('users-basic');

        /*
         * REGULAR USERS
         *  -> extend from minimal (user1)
         *  -> default settings
         */
        $users = UserFactory::createSequence(
            function () {
                foreach (range(1, 4) as $i) {
                    yield [
                        'username' => "user{$i}",
                        'email' => "user{$i}@studybuddy.test",
                        'displayName' => "User {$i}",
                        'password' => 'secret',
                    ];
                }
            });

        $this->addToPool('users-basic', $users);

        //UserFactory::createSequence();

        /*
         * SUBJECTS
         */
        $totalSubjects = 20;
        $subjects = SubjectFactory::new()
            ->sequence(
                fn(int $i) => ['name' => "subject$i"]
            )
            ->distribute(
                'user',
                self::getPool('users-basic')
            );

        /*
         * TASKS
         *
        $totalTasks = 15;
        for($i = 1; $i < $totalTasks; $i++){
            "task$i",
            ,
            'tasks-basic'
        }

        /*
         * NOTES
         *
        $totalNotes = 15;
        for($i = 1; $i < $totalNotes; $i++){
            "note$i",
            ,
            'notes-basic'
        }

        //TODO: add resources,sessions,sessionEntries,tags

        */
    }
}
