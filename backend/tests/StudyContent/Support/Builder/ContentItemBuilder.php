<?php

declare(strict_types=1);

namespace App\Tests\StudyContent\Support\Builder;

use App\StudyContent\Domain\Model\ContentItem\ContentBody;
use App\StudyContent\Domain\Model\ContentItem\ContentItem;
use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\StudyContent\Domain\Model\ContentItem\ContentKind;
use App\StudyContent\Domain\Model\ContentItem\ContentTitle;
use App\StudyContent\Domain\Model\ContentItem\DerivationMethod;
use App\StudyContent\Domain\Model\ContentItem\OwnerId;
use App\StudyContent\Domain\Model\ContentItem\SharingScope;
use App\StudyContent\Domain\Model\ContentItem\SourceReference;

final class ContentItemBuilder
{
    private string $id = 'content-1';
    private string $ownerId = 'owner-1';
    private ContentKind $kind = ContentKind::NOTE;
    private string $title = 'Test content';
    private string $body = 'Body';
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

    public function withKind(ContentKind $kind): self
    {
        $clone = clone $this;
        $clone->kind = $kind;
        return $clone;
    }

    public function buildDraft(): ContentItem
    {
        return ContentItem::createDraft(
            id: ContentItemId::fromString($this->id),
            ownerId: new OwnerId($this->ownerId),
            kind: $this->kind,
            title: new ContentTitle($this->title),
            body: new ContentBody($this->body),
            scope: $this->scope
        );
    }

    public function buildImported(): ContentItem
    {
        return ContentItem::importSourceMaterial(
            id: ContentItemId::fromString($this->id),
            ownerId: new OwnerId($this->ownerId),
            title: new ContentTitle($this->title),
            scope: $this->scope,
            origin: SourceReference::uploadedFile('fixture.pdf'),
            body: null
        );
    }

    /**
     * @param list<string> $sourceIds
     */
    public function buildDerivative(array $sourceIds): ContentItem
    {
        return ContentItem::createDerivative(
            id: ContentItemId::fromString($this->id),
            ownerId: new OwnerId($this->ownerId),
            kind: $this->kind,
            title: new ContentTitle($this->title),
            body: new ContentBody($this->body),
            scope: $this->scope,
            sourceIds: array_map(
                fn (string $id) => ContentItemId::fromString($id),
                $sourceIds
            ),
            derivationMethod: DerivationMethod::SUMMARIZED
        );
    }
}
