<?php

declare(strict_types=1);

namespace App\Tests\StudyContent\Domain\Service;

use App\StudyContent\Domain\Exception\PublicationPolicyViolation;
use App\StudyContent\Domain\Model\ContentItem\CanonicalStatus;
use App\StudyContent\Domain\Model\ContentItem\PolicyDecisionReason;
use App\StudyContent\Domain\Model\ContentItem\PublicationChannel;
use App\StudyContent\Domain\Model\ContentItem\SharingScope;
use App\StudyContent\Domain\Service\DefaultPublicationPolicyService;
use App\Tests\StudyContent\Support\Builder\ContentItemBuilder;
use App\Tests\StudyContent\Support\Double\InMemoryContentItemRepository;
use PHPUnit\Framework\TestCase;

final class DefaultPublicationPolicyServiceTest extends TestCase
{
    public function test_personal_content_cannot_be_published_to_group_library(): void
    {
        $repo = new InMemoryContentItemRepository();
        $service = new DefaultPublicationPolicyService($repo);

        $item = (new ContentItemBuilder())
            ->withScope(SharingScope::PERSONAL)
            ->buildDraft();

        $item->submitForReview();
        $item->approve('reviewer-1', new PolicyDecisionReason('Approved.'));

        $this->expectException(PublicationPolicyViolation::class);

        $service->assertContentCanBePublished($item, PublicationChannel::GROUP_LIBRARY);
    }

    public function test_duplicate_of_canonical_cannot_be_published_to_shared_channel(): void
    {
        $repo = new InMemoryContentItemRepository();
        $service = new DefaultPublicationPolicyService($repo);

        $item = (new ContentItemBuilder())
            ->withScope(SharingScope::SHARED_GROUP)
            ->buildDraft();

        $item->submitForReview();
        $item->approve('reviewer-1', new PolicyDecisionReason('Approved.'));
        $item->markDuplicateOfCanonical(
            \App\StudyContent\Domain\Model\ContentItem\ContentItemId::fromString('canonical-1')
        );

        $this->expectException(PublicationPolicyViolation::class);

        $service->assertContentCanBePublished($item, PublicationChannel::GROUP_LIBRARY);
    }
}
