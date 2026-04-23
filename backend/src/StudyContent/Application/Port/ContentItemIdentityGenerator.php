<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Port;

use App\StudyContent\Domain\Model\ContentItem\ContentItemId;

interface ContentItemIdentityGenerator
{
    public function next(): ContentItemId;
}
