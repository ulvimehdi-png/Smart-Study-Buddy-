<?php

namespace App\Story;

use App\Entity\User;
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
        $this->addToPool(
            'admin',
            UserFactory::new()
                ->admin()
                ->create()
        );

        /*
         * REGULAR USER
         *  -> fully default/empty
         */
        $this->addToPool(
            'users-basic',
            UserFactory::createSequence(
                ['username' => 'user1']
            )
        );
    }
}
