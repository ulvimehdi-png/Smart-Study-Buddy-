<?php

namespace App\Story;

use App\Factory\UserFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'minimalStory', groups: ['minimal'])]
final class MinimalStory extends Story
{
    public function build(): void
    {

        /*
         * ADMIN USER
         */
        UserFactory::new()
            ->admin()
            ->create();

        /*
         * REGULAR USERS
         *  -> fully default/empty
         */
        UserFactory::new()
            ->createOne([
                'username' => 'user1',
                ]);
    }
}
