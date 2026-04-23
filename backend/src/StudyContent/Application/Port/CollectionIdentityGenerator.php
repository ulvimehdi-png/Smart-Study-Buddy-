<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Port;

use App\StudyContent\Domain\Model\Collection\CollectionId;

interface CollectionIdentityGenerator
{
    public function next(): CollectionId;
}
