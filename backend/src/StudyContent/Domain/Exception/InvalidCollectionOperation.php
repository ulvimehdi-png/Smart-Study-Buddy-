<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Exception;

use App\StudyContent\Domain\Model\Collection\CollectionStatus;

final class InvalidCollectionOperation
    extends DomainException
{
    public static function fromStatus(
        CollectionStatus $status,
        string           $action,
    ): self
    {
        return new self(sprintf('Action "%s" is not allowed from collection status "%s".',
            $action, $status->value,),);
    }
}
