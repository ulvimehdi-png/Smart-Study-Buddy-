<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Service;

use App\StudyContent\Domain\Exception\CollectionPolicyViolation;
use App\StudyContent\Domain\Model\Collection\Collection;
use App\StudyContent\Domain\Model\Collection\CollectionStatus;
use App\StudyContent\Domain\Model\ContentItem\CanonicalStatus;
use App\StudyContent\Domain\Model\ContentItem\ContentItem;
use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\StudyContent\Domain\Model\ContentItem\LifecycleStatus;
use App\StudyContent\Domain\Model\ContentItem\PublicationChannel;
use App\StudyContent\Domain\Model\ContentItem\SharingScope;
use App\StudyContent\Domain\Repository\ContentItemRepository;

final readonly class DefaultCollectionMembershipPolicy
    implements CollectionMembershipPolicy
{
    public function __construct(
        private ContentItemRepository    $contentItems,
        private PublicationPolicyService $publicationPolicy,
    )
    {
    }

    public function assertCollectionCanBePublished(
        Collection $collection,
    ): void
    {
        $contentIds = array_map(static fn(
            $entry,
        ): ContentItemId => $entry->contentItemId(), $collection->entries(),);

        $members = $this->contentItems->getByIds($contentIds);

        $resolvedById = [];
        foreach ($members as $member) {
            $resolvedById[$member
                ->id()->toString()] = $member;
        }

        foreach ($contentIds as $contentId) {
            $member = $resolvedById[$contentId->toString()] ?? null;

            if ($member === null) {
                throw CollectionPolicyViolation::missingMember($contentId->toString());
            }

            $this->assertCanBeAdded($collection, $member);

            $this->publicationPolicy->assertContentCanBePublished($member,
                $this->channelForCollectionScope($collection->scope()),);
        }
    }

    public function assertCanBeAdded(
        Collection  $collection,
        ContentItem $contentItem,
    ): void
    {
        $contentId = $contentItem
            ->id()->toString();

        if ($contentItem->lifecycleStatus() === LifecycleStatus::BLOCKED) {
            throw CollectionPolicyViolation::blockedContentCannotBeAdded($contentId);
        }

        if ($collection->scope() === SharingScope::SHARED_GROUP
            && $contentItem->scope() === SharingScope::PERSONAL) {
            throw CollectionPolicyViolation::personalContentCannotEnterSharedCollection($contentId);
        }

        if ($collection->scope() === SharingScope::INSTITUTION_MANAGED
            && $contentItem->scope() !== SharingScope::INSTITUTION_MANAGED) {
            throw CollectionPolicyViolation::onlyInstitutionManagedContentMayEnterInstitutionCollection($contentId);
        }

        if ($collection->scope() === SharingScope::INSTITUTION_MANAGED
            && $contentItem->canonicalStatus()
            === CanonicalStatus::DUPLICATE_OF_CANONICAL) {
            throw CollectionPolicyViolation::canonicalDuplicateCannotEnterInstitutionCollection($contentId);
        }

        if ($collection->status() === CollectionStatus::PUBLISHED
            && $contentItem->lifecycleStatus() === LifecycleStatus::DEPRECATED) {
            throw CollectionPolicyViolation::deprecatedContentCannotEnterPublishedCollection($contentId);
        }

        if ($collection->status() === CollectionStatus::PUBLISHED
            && $contentItem->lifecycleStatus() === LifecycleStatus::ARCHIVED) {
            throw CollectionPolicyViolation::archivedContentCannotEnterPublishedCollection($contentId);
        }
    }

    private function channelForCollectionScope(
        SharingScope $scope,
    ): PublicationChannel
    {
        return match ($scope) {
            SharingScope::PERSONAL => PublicationChannel::PRIVATE_LIBRARY,
            SharingScope::SHARED_GROUP => PublicationChannel::GROUP_LIBRARY,
            SharingScope::INSTITUTION_MANAGED => PublicationChannel::INSTITUTION_LIBRARY,
        };
    }
}
