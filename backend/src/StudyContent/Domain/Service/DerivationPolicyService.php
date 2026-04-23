<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Service;

use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\StudyContent\Domain\Model\ContentItem\ContentKind;

interface DerivationPolicyService
{
    /**
     * @param list<ContentItemId> $sourceIds
     */
    public function assertCanDeriveFromSources(
        array       $sourceIds,
        ContentKind $targetKind,
    ): void;
}
