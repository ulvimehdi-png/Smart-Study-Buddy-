<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Port;

interface TransactionRunner
{
    /**
     * @template T
     * @param callable():T $callback
     *
     * @return T
     */
    public function run(callable $callback): mixed;
}
