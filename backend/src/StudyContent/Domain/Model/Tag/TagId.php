<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\Tag;

class TagId
{
    public function __construct(
        public int $id,
    )
    {
    }
}
