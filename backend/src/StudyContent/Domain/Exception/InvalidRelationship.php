<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Exception;

use App\StudyContent\Domain\Model\ContentItem\RelationshipType;

final class InvalidRelationship
    extends DomainException
{
    public static function selfReference(RelationshipType $type): self
    {
        return new self(sprintf('Self relationship is not allowed for relationship type "%s".',
            $type->value,),);
    }

    public static function duplicate(
        RelationshipType $type,
        string           $targetId,
    ): self
    {
        return new self(sprintf('Relationship "%s" to target "%s" already exists.',
            $type->value, $targetId,),);
    }
}
