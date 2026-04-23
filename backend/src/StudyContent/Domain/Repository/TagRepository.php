<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Repository;

use App\StudyContent\Domain\Model\Tag\Tag;
use App\StudyContent\Domain\Model\Tag\TagId;

interface TagRepository
{
    public function save(Tag $tag): void;

    public function get(TagId $id): Tag;

    public function find(TagId $id): ?Tag;
}
