<?php

declare(strict_types=1);

namespace App\Tests\StudyContent\Support\Double;

use App\StudyContent\Application\Port\CollectionIdentityGenerator;
use App\StudyContent\Domain\Model\Collection\CollectionId;

final class StubCollectionIdentityGenerator implements CollectionIdentityGenerator
{
    /** @var list<string> */
    private array $ids;

    /**
     * @param list<string> $ids
     */
    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function next(): CollectionId
    {
        $value = array_shift($this->ids);

        if ($value === null) {
            throw new \RuntimeException('No more stub collection IDs configured.');
        }

        return CollectionId::fromString($value);
    }
}
