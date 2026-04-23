<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Persistence\Doctrine\Repository;

use App\StudyContent\Domain\Model\Collection\Collection;
use App\StudyContent\Domain\Model\Collection\CollectionId;
use App\StudyContent\Domain\Repository\CollectionRepository;
use App\StudyContent\Infrastructure\Persistence\Doctrine\Entity\CollectionRecord;
use App\StudyContent\Infrastructure\Persistence\Doctrine\Mapper\CollectionMapper;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineCollectionRepository implements CollectionRepository
{
    public function __construct(
        private EntityManagerInterface $em,
        private CollectionMapper $mapper
    ) {
    }

    public function save(Collection $collection): void
    {
        /** @var CollectionRecord|null $existing */
        $existing = $this->em->find(CollectionRecord::class, $collection->id()->toString());

        $record = $this->mapper->toRecord($collection, $existing);
        $this->em->persist($record);
    }

    public function get(CollectionId $id): Collection
    {
        $record = $this->em->getRepository(CollectionRecord::class)
            ->createQueryBuilder('c')
            ->leftJoin('c.entries', 'e')->addSelect('e')
            ->leftJoin('c.publications', 'p')->addSelect('p')
            ->where('c.id = :id')
            ->setParameter('id', $id->toString())
            ->getQuery()
            ->getOneOrNullResult();

        if (!$record instanceof CollectionRecord) {
            throw new \RuntimeException(sprintf('Collection "%s" not found.', $id->toString()));
        }

        return $this->mapper->toDomain($record);
    }

    public function find(CollectionId $id): ?Collection
    {
        try {
            return $this->get($id);
        } catch (\RuntimeException) {
            return null;
        }
    }
}
