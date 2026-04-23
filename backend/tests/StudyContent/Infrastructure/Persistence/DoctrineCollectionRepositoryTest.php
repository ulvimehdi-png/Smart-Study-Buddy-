<?php

declare(strict_types=1);

namespace App\Tests\StudyContent\Infrastructure\Persistence;

use App\StudyContent\Domain\Model\Collection\CollectionId;
use App\StudyContent\Infrastructure\Persistence\Doctrine\Repository\DoctrineCollectionRepository;
use App\Tests\StudyContent\Support\Builder\CollectionBuilder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineCollectionRepositoryTest extends KernelTestCase
{
    private DoctrineCollectionRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->repository = $container->get(DoctrineCollectionRepository::class);
    }

    public function test_roundtrip_preserves_entry_order(): void
    {
        $collection = (new CollectionBuilder())
            ->withId('22222222-2222-2222-2222-222222222222')
            ->build();

        $collection->addEntry(\App\StudyContent\Domain\Model\ContentItem\ContentItemId::fromString('content-1'));
        $collection->addEntry(\App\StudyContent\Domain\Model\ContentItem\ContentItemId::fromString('content-2'));
        $collection->reorder(
            \App\StudyContent\Domain\Model\ContentItem\ContentItemId::fromString('content-2'),
            new \App\StudyContent\Domain\Model\Collection\CollectionEntryPosition(1)
        );

        $this->repository->save($collection);

        $reloaded = $this->repository->get(
            CollectionId::fromString('22222222-2222-2222-2222-222222222222')
        );

        self::assertSame('content-2', $reloaded->entries()[0]->contentItemId()->toString());
        self::assertSame('content-1', $reloaded->entries()[1]->contentItemId()->toString());
    }
}
