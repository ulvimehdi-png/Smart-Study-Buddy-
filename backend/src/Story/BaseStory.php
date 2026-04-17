<?php

namespace App\Story;

use App\Factory\UserFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'baseStory', groups: ['base'])]
final class BaseStory extends Story
{
    public function build(): void
    {
        MinimalStory::load();

        /*
         * REGULAR USERS
         *  -> extend from minimal
         *  -> deterministic
         */
        UserFactory::new()
            ->many(4)
            ->create(fn (int $i) => [
                'username' => "user".($i+1),
                'email' => "user".($i+1)."@studybuddy.com",
                'displayName' => "displayname".($i+1),
            ]);

        /*
         * SUBJECTS
         */
    }
}
