<?php

namespace App\General\Data\Story;

use App\Feature\User\Factory\UserFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'minimal', groups: ['all'])]
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
        );

        /*
         * REGULAR USER
         *  -> fully default/empty
         */
        $this->addToPool(
            'users-basic',
            UserFactory::createOne([
                'username' => 'user1',
            ])
        );
    }
}
