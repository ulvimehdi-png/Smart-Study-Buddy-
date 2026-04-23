<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Exception;

use App\StudyContent\Domain\Model\ContentItem\LifecycleStatus;

final class InvalidLifeCycleTransition
    extends DomainException
{
    public static function fromTo(
        LifecycleStatus $from,
        string          $action,
    ): self
    {
        return new self(sprintf('Action "%s" is not allowed from lifecycle status "%s".',
            $action, $from->value,),);
    }
}
