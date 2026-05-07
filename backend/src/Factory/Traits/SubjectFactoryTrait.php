<?php

namespace App\Factory\Traits;

use App\Entity\Subject;

trait SubjectFactoryTrait
{
    public function forSubject(?Subject $subject): static
    {
        return $this->with([
            'subject' => $subject,
        ]);
    }
}
