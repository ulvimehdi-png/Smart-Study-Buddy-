<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Service;

use App\StudyContent\Domain\Exception\PublicationPolicyViolation;
use App\StudyContent\Domain\Model\ContentItem\CanonicalStatus;
use App\StudyContent\Domain\Model\ContentItem\ContentItem;
use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\StudyContent\Domain\Model\ContentItem\LifecycleStatus;
use App\StudyContent\Domain\Model\ContentItem\PublicationChannel;
use App\StudyContent\Domain\Model\ContentItem\SharingScope;
use App\StudyContent\Domain\Repository\ContentItemRepository;

final readonly class DefaultPublicationPolicyService
    implements PublicationPolicyService
{
    public function __construct(
        private ContentItemRepository $contentItems,
    )
    {
    }

    public function assertContentCanBePublished(
        ContentItem        $contentItem,
        PublicationChannel $channel,
    ): void
    {
        $this->assertChannelCompatibleWithScope($contentItem, $channel);
        $this->assertCanonicalPublicationRule($contentItem, $channel);
        $this->assertNotSuperseded($contentItem);
        $this->assertNoBlockedSources($contentItem);
    }

    private function assertChannelCompatibleWithScope(
        ContentItem        $contentItem,
        PublicationChannel $channel,
    ): void
    {
        $scope = $contentItem->scope();

        match ($channel) {
            PublicationChannel::PRIVATE_LIBRARY => null,
            PublicationChannel::GROUP_LIBRARY => $this->assertNotPersonalScope($scope),
            PublicationChannel::INSTITUTION_LIBRARY => $this->assertInstitutionManagedScope($scope),
        };
    }

    private function assertNotPersonalScope(
        SharingScope $scope,
    ): void
    {
        if ($scope === SharingScope::PERSONAL) {
            throw PublicationPolicyViolation::personalScopeCannotUseSharedChannel();
        }
    }

    private function assertInstitutionManagedScope(
        SharingScope $scope,
    ): void
    {
        if ($scope !== SharingScope::INSTITUTION_MANAGED) {
            throw PublicationPolicyViolation::onlyInstitutionManagedMayUseInstitutionChannel();
        }
    }

    private function assertCanonicalPublicationRule(
        ContentItem        $contentItem,
        PublicationChannel $channel,
    ): void
    {
        if ($contentItem->canonicalStatus() === CanonicalStatus::DUPLICATE_OF_CANONICAL
            && $channel !== PublicationChannel::PRIVATE_LIBRARY) {
            throw PublicationPolicyViolation::canonicalDuplicateCannotBeShared();
        }
    }

    private function assertNotSuperseded(
        ContentItem $contentItem,
    ): void
    {
        if ($contentItem->supersession() !== null) {
            throw PublicationPolicyViolation::supersededContentCannotBePublished();
        }
    }

    private function assertNoBlockedSources(
        ContentItem $contentItem,
    ): void
    {
        foreach ($contentItem
                     ->Provenance()->sources() as $provenanceSource) {
            $source = $provenanceSource->source();

            if (!$source instanceof ContentItemId) {
                continue;
            }

            $sourceItem = $this->contentItems->get($source);

            if ($sourceItem->lifecycleStatus() === LifecycleStatus::BLOCKED) {
                throw PublicationPolicyViolation::blockedSourcePreventsPublication($source->toString());
            }
        }
    }
}
