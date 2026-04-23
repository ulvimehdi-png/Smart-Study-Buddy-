<?php

declare(strict_types=1);

namespace App\Tests\StudyContent\Infrastructure\Persistence;

use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\StudyContent\Domain\Model\ContentItem\PolicyDecisionReason;
use App\StudyContent\Domain\Model\ContentItem\PublicationChannel;
use App\StudyContent\Infrastructure\Persistence\Doctrine\Repository\DoctrineContentItemRepository;
use App\Tests\StudyContent\Support\Builder\ContentItemBuilder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineContentItemRepositoryTest extends KernelTestCase
{
    private DoctrineContentItemRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->repository = $container->get(DoctrineContentItemRepository::class);
    }

    public function test_roundtrip_preserves_core_state(): void
    {
        $item = (new ContentItemBuilder())
            ->withId('11111111-1111-1111-1111-111111111111')
            ->buildDraft();

        $item->submitForReview();
        $item->approve('reviewer-1', new PolicyDecisionReason('Approved.'));
        $item->publish(PublicationChannel::PRIVATE_LIBRARY, 'reviewer-1');

        $this->repository->save($item);

        $reloaded = $this->repository->get(
            ContentItemId::fromString('11111111-1111-1111-1111-111111111111')
        );

        self::assertSame('published', $reloaded->lifecycleStatus()->value);
        self::assertCount(1, $reloaded->reviewHistory());
        self::assertCount(1, $reloaded->publicationHistory());
        self::assertSame($item->title()->toString(), $reloaded->title()->toString());
    }
}
