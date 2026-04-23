<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Persistence\Doctrine\Identity;

use App\StudyContent\Application\Port\CollectionIdentityGenerator;
use App\StudyContent\Domain\Model\Collection\CollectionId;
use Symfony\Component\Uid\Uuid;

final class UuidCollectionIdentityGenerator implements CollectionIdentityGenerator
{
    public function next(): CollectionId
    {
        return CollectionId::fromString(Uuid::v7()->toRfc4122());
    }
}
