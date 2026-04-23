<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Persistence\Doctrine\Repository;

use App\StudyContent\Domain\Model\ContentItem\ContentItem;
use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\StudyContent\Domain\Repository\ContentItemRepository;
use App\StudyContent\Infrastructure\Persistence\Doctrine\Entity\ContentItemRecord;
use App\StudyContent\Infrastructure\Persistence\Doctrine\Mapper\ContentItemMapper;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineContentItemRepository implements ContentItemRepository
{
    public function __construct(
        private EntityManagerInterface $em,
        private ContentItemMapper $mapper
    ) {
    }

    public function save(ContentItem $contentItem): void
    {
        /** @var ContentItemRecord|null $existing */
        $existing = $this->em->find(ContentItemRecord::class, $contentItem->id()->toString());

        $record = $this->mapper->toRecord($contentItem, $existing);
        $this->em->persist($record);
    }

    public function get(ContentItemId $id): ContentItem
    {
        $record = $this->em->getRepository(ContentItemRecord::class)
            ->createQueryBuilder('c')
            ->leftJoin('c.provenanceSources', 'ps')->addSelect('ps')
            ->leftJoin('c.relationships', 'r')->addSelect('r')
            ->leftJoin('c.reviews', 'rv')->addSelect('rv')
            ->leftJoin('c.publications', 'p')->addSelect('p')
            ->where('c.id = :id')
            ->setParameter('id', $id->toString())
            ->getQuery()
            ->getOneOrNullResult();

        if (!$record instanceof ContentItemRecord) {
            throw new \RuntimeException(sprintf('Content item "%s" not found.', $id->toString()));
        }

        return $this->mapper->toDomain($record);
    }

    public function find(ContentItemId $id): ?ContentItem
    {
        try {
            return $this->get($id);
        } catch (\RuntimeException) {
            return null;
        }
    }

    public function getByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $rawIds = array_map(
            static fn (ContentItemId $id): string => $id->toString(),
            $ids
        );

        $records = $this->em->getRepository(ContentItemRecord::class)
            ->createQueryBuilder('c')
            ->leftJoin('c.provenanceSources', 'ps')->addSelect('ps')
            ->leftJoin('c.relationships', 'r')->addSelect('r')
            ->leftJoin('c.reviews', 'rv')->addSelect('rv')
            ->leftJoin('c.publications', 'p')->addSelect('p')
            ->where('c.id IN (:ids)')
            ->setParameter('ids', $rawIds)
            ->getQuery()
            ->getResult();

        return array_map(
            fn (ContentItemRecord $record): ContentItem => $this->mapper->toDomain($record),
            $records
        );
    }
}
