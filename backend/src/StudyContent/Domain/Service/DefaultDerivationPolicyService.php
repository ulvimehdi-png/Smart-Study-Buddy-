<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Service;

use App\StudyContent\Domain\Exception\DerivationPolicyViolation;
use App\StudyContent\Domain\Model\ContentItem\ContentKind;
use App\StudyContent\Domain\Model\ContentItem\LifecycleStatus;
use App\StudyContent\Domain\Repository\ContentItemRepository;

final readonly class DefaultDerivationPolicyService
    implements DerivationPolicyService
{
    public function __construct(
        private ContentItemRepository $contentItems,
    )
    {
    }

    public function assertCanDeriveFromSources(
        array       $sourceIds,
        ContentKind $targetKind,
    ): void
    {
        if ($targetKind === ContentKind::SOURCE_DOCUMENT) {
            throw DerivationPolicyViolation::targetKindCannotBeSourceDocument();
        }

        $sources = $this->contentItems->getByIds($sourceIds);

        foreach ($sources as $source) {
            if ($source->lifecycleStatus() === LifecycleStatus::BLOCKED) {
                throw DerivationPolicyViolation::blockedSource($source
                    ->id()->toString(),);
            }

            if ($source->lifecycleStatus() === LifecycleStatus::ARCHIVED) {
                throw DerivationPolicyViolation::archivedSource($source
                    ->id()->toString(),);
            }
        }
    }
}
