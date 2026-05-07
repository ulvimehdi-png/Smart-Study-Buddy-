<?php

namespace App\Factory\Traits;

use App\Entity\User;

trait UserFactoryTrait
{
    public function forUser(User $user): static
    {
        return $this->with([
            'user' => $user,
        ]);
    }
}
