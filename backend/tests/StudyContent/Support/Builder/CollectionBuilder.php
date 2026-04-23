<?php

declare(strict_types=1);

namespace App\Tests\StudyContent\Support\Builder;

use App\StudyContent\Domain\Model\Collection\Collection;
use App\StudyContent\Domain\Model\Collection\CollectionId;
use App\StudyContent\Domain\Model\Collection\CollectionTitle;
use App\StudyContent\Domain\Model\ContentItem\OwnerId;
use App\StudyContent\Domain\Model\ContentItem\SharingScope;

final class CollectionBuilder
{
    private string $id = 'collection-1';
    private string $ownerId = 'owner-1';
    private string $title = 'Test collection';
    private SharingScope $scope = SharingScope::PERSONAL;

    public function withId(string $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function withScope(SharingScope $scope): self
    {
        $clone = clone $this;
        $clone->scope = $scope;
        return $clone;
    }

    public function build(): Collection
    {
        return Collection::create(
            id: CollectionId::fromString($this->id),
            title: new CollectionTitle($this->title),
            ownerId: new OwnerId($this->ownerId),
            scope: $this->scope
        );
    }
}
