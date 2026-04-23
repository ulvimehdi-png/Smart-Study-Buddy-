<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\Collection;

use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use DateTimeImmutable;
use InvalidArgumentException;

final class CollectionEntry
{
    public function __construct(
        private ContentItemId           $contentItemId,
        private CollectionEntryPosition $position,
        private DateTimeImmutable       $addedAt,
        private ?string                 $curationNote = null,
    )
    {
        if ($curationNote !== null && mb_strlen(trim($curationNote)) > 1000) {
            throw new InvalidArgumentException('Curation note is too long.',);
        }
    }

    public function contentItemId(): ContentItemId
    {
        return $this->contentItemId;
    }

    public function position(): CollectionEntryPosition
    {
        return $this->position;
    }

    public function moveTo(CollectionEntryPosition $position): void
    {
        $this->position = $position;
    }

    public function addedAt(): DateTimeImmutable
    {
        return $this->addedAt;
    }

    public function curationNote(): ?string
    {
        return $this->curationNote;
    }

    public function sameContent(ContentItemId $other): bool
    {
        return $this->contentItemId->equals($other);
    }
}
