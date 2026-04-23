<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Persistence\Doctrine\Identity;

use App\StudyContent\Application\Port\ContentItemIdentityGenerator;
use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use Symfony\Component\Uid\Uuid;

final class UuidContentItemIdentityGenerator implements ContentItemIdentityGenerator
{
    public function next(): ContentItemId
    {
        return ContentItemId::fromString(Uuid::v7()->toRfc4122());
    }
}
