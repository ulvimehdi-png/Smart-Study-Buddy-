<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Port;

interface ContentIndexer
{
    public function upsert(IndexableContentDocument $document): void;

    public function remove(string $id): void;
}

final readonly class IndexableContentDocument
{
    public function __construct(
        public string  $id,
        public string  $title,
        public ?string $body,
        public string  $kind,
        public string  $scope,
        public string  $lifecycleStatus
    )
    {
    }
}
