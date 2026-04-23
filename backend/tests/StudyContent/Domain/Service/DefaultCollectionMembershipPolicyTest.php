<?php

declare(strict_types=1);

namespace App\Tests\StudyContent\Domain\Service;

use App\StudyContent\Domain\Exception\CollectionPolicyViolation;
use App\StudyContent\Domain\Model\ContentItem\PolicyDecisionReason;
use App\StudyContent\Domain\Model\ContentItem\PublicationChannel;
use App\StudyContent\Domain\Model\ContentItem\SharingScope;
use App\StudyContent\Domain\Service\DefaultCollectionMembershipPolicy;
use App\StudyContent\Domain\Service\DefaultPublicationPolicyService;
use App\Tests\StudyContent\Support\Builder\CollectionBuilder;
use App\Tests\StudyContent\Support\Builder\ContentItemBuilder;
use App\Tests\StudyContent\Support\Double\InMemoryContentItemRepository;
use PHPUnit\Framework\TestCase;

final class DefaultCollectionMembershipPolicyTest extends TestCase
{
    public function test_personal_content_cannot_be_added_to_shared_group_collection(): void
    {
        $repo = new InMemoryContentItemRepository();
        $publicationPolicy = new DefaultPublicationPolicyService($repo);
        $membershipPolicy = new DefaultCollectionMembershipPolicy($repo, $publicationPolicy);

        $collection = (new CollectionBuilder())
            ->withScope(SharingScope::SHARED_GROUP)
            ->build();

        $content = (new ContentItemBuilder())
            ->withScope(SharingScope::PERSONAL)
            ->buildDraft();

        $this->expectException(CollectionPolicyViolation::class);

        $membershipPolicy->assertCanBeAdded($collection, $content);
    }

    public function test_collection_can_be_published_when_member_is_publishable(): void
    {
        $repo = new InMemoryContentItemRepository();
        $publicationPolicy = new DefaultPublicationPolicyService($repo);
        $membershipPolicy = new DefaultCollectionMembershipPolicy($repo, $publicationPolicy);

        $collection = (new CollectionBuilder())
            ->withScope(SharingScope::SHARED_GROUP)
            ->build();

        $content = (new ContentItemBuilder())
            ->withId('content-1')
            ->withScope(SharingScope::SHARED_GROUP)
            ->buildDraft();

        $content->submitForReview();
        $content->approve('reviewer-1', new PolicyDecisionReason('Approved.'));
        $repo->save($content);

        $collection->addEntry($content->id());

        $membershipPolicy->assertCollectionCanBePublished($collection);

        self::assertTrue(true);
    }
}
