<?php

namespace App\Factory\Traits;

use App\Entity\Task;

trait TaskFactoryTrait
{
    public function forTask(?Task $task): static
    {
        return $this->with([
            'task' => $task,
        ]);
    }
}
