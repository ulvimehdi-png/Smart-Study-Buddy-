<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\Subject;

class SubjectId
{
    public function __construct(
        public int $id,
    )
    {
    }
}
