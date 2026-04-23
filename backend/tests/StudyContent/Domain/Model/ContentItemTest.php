<?php

declare(strict_types=1);

namespace App\Tests\StudyContent\Domain\Model;

use App\StudyContent\Domain\Exception\ReviewRequired;
use App\StudyContent\Domain\Model\ContentItem\PolicyDecisionReason;
use App\StudyContent\Domain\Model\ContentItem\PublicationChannel;
use App\Tests\StudyContent\Support\Builder\ContentItemBuilder;
use PHPUnit\Framework\TestCase;

final class ContentItemTest extends TestCase
{
    public function test_imported_content_can_be_processed(): void
    {
        $item = (new ContentItemBuilder())->buildImported();

        $item->markProcessed();

        self::assertSame('processed', $item->lifecycleStatus()->value);
        self::assertSame(2, $item->revisionNumber()->toInt());
    }

    public function test_approved_content_can_be_published(): void
    {
        $item = (new ContentItemBuilder())->buildDraft();
        $item->submitForReview();
        $item->approve('reviewer-1', new PolicyDecisionReason('Looks correct.'));

        $item->publish(PublicationChannel::PRIVATE_LIBRARY, 'reviewer-1');

        self::assertSame('published', $item->lifecycleStatus()->value);
        self::assertCount(1, $item->publicationHistory());
    }

    public function test_content_requiring_approval_cannot_be_published_before_approval(): void
    {
        $item = (new ContentItemBuilder())->buildDraft();

        $this->expectException(ReviewRequired::class);

        $item->publish(PublicationChannel::PRIVATE_LIBRARY, 'reviewer-1');
    }

    public function test_revising_published_content_returns_it_to_draft(): void
    {
        $item = (new ContentItemBuilder())->buildDraft();
        $item->submitForReview();
        $item->approve('reviewer-1', new PolicyDecisionReason('Approved.'));
        $item->publish(PublicationChannel::PRIVATE_LIBRARY, 'reviewer-1');

        $item->revise($item->title(), $item->body());

        self::assertSame('draft', $item->lifecycleStatus()->value);
    }
}
