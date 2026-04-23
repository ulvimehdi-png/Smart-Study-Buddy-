<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Persistence\Doctrine\Transaction;

use App\StudyContent\Application\Port\TransactionRunner;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineTransactionRunner implements TransactionRunner
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function run(callable $callback): mixed
    {
        return $this->entityManager->wrapInTransaction(function () use ($callback) {
            $result = $callback();
            $this->entityManager->flush();

            return $result;
        });
    }
}
