<?php

declare(strict_types=1);

namespace App\Tests\StudyContent\Support\Double;

use App\StudyContent\Application\Port\ContentItemIdentityGenerator;
use App\StudyContent\Domain\Model\ContentItem\ContentItemId;

final class StubContentItemIdentityGenerator implements ContentItemIdentityGenerator
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

    public function next(): ContentItemId
    {
        $value = array_shift($this->ids);

        if ($value === null) {
            throw new \RuntimeException('No more stub content item IDs configured.');
        }

        return ContentItemId::fromString($value);
    }
}
