<?php

declare(strict_types=1);

namespace App\Tests\StudyContent\Application\Command\ImportSourceMaterial;

use App\StudyContent\Application\Command\ImportSourceMaterial\ImportSourceMaterial;
use App\StudyContent\Application\Command\ImportSourceMaterial\ImportSourceMaterialHandler;
use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\StudyContent\Domain\Model\ContentItem\SharingScope;
use App\Tests\StudyContent\Support\Double\CollectingEventBus;
use App\Tests\StudyContent\Support\Double\FakeBinaryAssetGateway;
use App\Tests\StudyContent\Support\Double\FixedClock;
use App\Tests\StudyContent\Support\Double\ImmediateTransactionRunner;
use App\Tests\StudyContent\Support\Double\InMemoryContentItemRepository;
use App\Tests\StudyContent\Support\Double\StubContentItemIdentityGenerator;
use PHPUnit\Framework\TestCase;

final class ImportSourceMaterialHandlerTest extends TestCase
{
    public function test_import_creates_imported_content_item_and_publishes_event(): void
    {
        $repository = new InMemoryContentItemRepository();
        $eventBus = new CollectingEventBus();

        $handler = new ImportSourceMaterialHandler(
            contentItems: $repository,
            identityGenerator: new StubContentItemIdentityGenerator(['content-1']),
            binaryAssets: new FakeBinaryAssetGateway(),
            clock: new FixedClock(new \DateTimeImmutable('2026-04-22T12:00:00+00:00')),
            transactions: new ImmediateTransactionRunner(),
            eventBus: $eventBus
        );

        $result = $handler(new ImportSourceMaterial(
            ownerId: 'owner-1',
            title: 'Lecture 1 PDF',
            scope: SharingScope::PERSONAL,
            originalFilename: 'lecture1.pdf',
            mimeType: 'application/pdf',
            temporaryPath: '/tmp/lecture1.pdf'
        ));

        $saved = $repository->get(ContentItemId::fromString('content-1'));

        self::assertSame('content-1', $result->id);
        self::assertSame('source_document', $saved->kind()->value);
        self::assertSame('imported', $saved->lifecycleStatus()->value);
        self::assertSame('Lecture 1 PDF', $saved->title()->toString());
        self::assertCount(1, $eventBus->released());
    }
}
