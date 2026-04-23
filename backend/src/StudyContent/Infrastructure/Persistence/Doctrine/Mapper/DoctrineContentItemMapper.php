<?php

declare(strict_types=1);

namespace App\StudyContent\Infrastructure\Persistence\Doctrine\Mapper;

use App\StudyContent\Domain\Model\ContentItem\CanonicalStatus;
use App\StudyContent\Domain\Model\ContentItem\ContentBody;
use App\StudyContent\Domain\Model\ContentItem\ContentItem;
use App\StudyContent\Domain\Model\ContentItem\ContentItemId;
use App\StudyContent\Domain\Model\ContentItem\ContentKind;
use App\StudyContent\Domain\Model\ContentItem\ContentTitle;
use App\StudyContent\Domain\Model\ContentItem\DerivationMethod;
use App\StudyContent\Domain\Model\ContentItem\LifecycleStatus;
use App\StudyContent\Domain\Model\ContentItem\OwnerId;
use App\StudyContent\Domain\Model\ContentItem\PolicyDecisionReason;
use App\StudyContent\Domain\Model\ContentItem\Provenance;
use App\StudyContent\Domain\Model\ContentItem\ProvenanceSource;
use App\StudyContent\Domain\Model\ContentItem\PublicationChannel;
use App\StudyContent\Domain\Model\ContentItem\PublicationPolicy;
use App\StudyContent\Domain\Model\ContentItem\PublicationRecord;
use App\StudyContent\Domain\Model\ContentItem\Relationship;
use App\StudyContent\Domain\Model\ContentItem\RelationshipType;
use App\StudyContent\Domain\Model\ContentItem\ReviewOutcome;
use App\StudyContent\Domain\Model\ContentItem\ReviewRecord;
use App\StudyContent\Domain\Model\ContentItem\RevisionNumber;
use App\StudyContent\Domain\Model\ContentItem\SharingScope;
use App\StudyContent\Domain\Model\ContentItem\SourceReference;
use App\StudyContent\Domain\Model\ContentItem\Supersession;
use App\StudyContent\Infrastructure\Persistence\Doctrine\Entity\ContentItemRecord;
use App\StudyContent\Infrastructure\Persistence\Doctrine\Entity\ContentProvenanceSourceRecord;
use App\StudyContent\Infrastructure\Persistence\Doctrine\Entity\ContentPublicationHistoryRecord;
use App\StudyContent\Infrastructure\Persistence\Doctrine\Entity\ContentRelationshipRecord;
use App\StudyContent\Infrastructure\Persistence\Doctrine\Entity\ContentReviewRecord;
use Doctrine\Common\Collections\ArrayCollection;

final class DoctrineContentItemMapper implements ContentItemMapper
{
    public function toDomain(ContentItemRecord $record): ContentItem
    {
        $provenanceSources = [];
        foreach ($record->provenanceSources as $row) {
            if ($row->sourceContentItemId !== null) {
                $source = ProvenanceSource::derivedFrom(
                    ContentItemId::fromString($row->sourceContentItemId),
                    DerivationMethod::from($row->derivationMethod),
                    $row->recordedAt
                );
            } else {
                $source = ProvenanceSource::importedFrom(
                    new SourceReference(
                        $row->sourceReferenceType ?? '',
                        $row->sourceReferenceValue ?? ''
                    ),
                    $row->recordedAt
                );
            }

            $provenanceSources[] = $source;
        }

        $provenance = Provenance::derived($provenanceSources);

        $reviews = [];
        foreach ($record->reviews as $row) {
            $reviews[] = new ReviewRecord(
                reviewerId: $row->reviewerId,
                outcome: ReviewOutcome::from($row->outcome),
                reason: new PolicyDecisionReason($row->reason),
                reviewedAt: $row->reviewedAt
            );
        }

        $publications = [];
        foreach ($record->publications as $row) {
            $publications[] = new PublicationRecord(
                channel: PublicationChannel::from($row->channel),
                scope: SharingScope::from($row->scope),
                publishedBy: $row->publishedBy,
                publishedAt: $row->publishedAt
            );
        }

        $relationships = [];
        foreach ($record->relationships as $row) {
            $relationships[] = new Relationship(
                type: RelationshipType::from($row->relationshipType),
                targetId: ContentItemId::fromString($row->targetContentItemId),
                recordedAt: $row->recordedAt
            );
        }

        $supersession = $record->supersededByContentItemId !== null
            ? new Supersession(
                ContentItemId::fromString($record->supersededByContentItemId),
                $record->supersededAt
            )
            : null;

        return ContentItem::reconstitute(
            id: ContentItemId::fromString($record->id),
            kind: ContentKind::from($record->kind),
            title: new ContentTitle($record->title),
            body: $record->body !== null ? new ContentBody($record->body) : null,
            ownerId: new OwnerId($record->ownerId),
            scope: SharingScope::from($record->scope),
            lifecycleStatus: LifecycleStatus::from($record->lifecycleStatus),
            canonicalStatus: CanonicalStatus::from($record->canonicalStatus),
            provenance: $provenance,
            publicationPolicy: new PublicationPolicy(
                requiresApproval: $record->publicationPolicyRequiresApproval,
                allowPersonal: $record->publicationPolicyAllowPersonal,
                allowSharedGroup: $record->publicationPolicyAllowSharedGroup,
                allowInstitutionManaged: $record->publicationPolicyAllowInstitutionManaged
            ),
            revisionNumber: new RevisionNumber($record->revisionNumber),
            supersession: $supersession,
            approvedAt: $record->approvedAt,
            blockedAt: $record->blockedAt,
            archivedAt: $record->archivedAt,
            reviewHistory: $reviews,
            publicationHistory: $publications,
            relationships: $relationships
        );
    }

    public function toRecord(
        ContentItem $aggregate,
        ?ContentItemRecord $existingRecord = null
    ): ContentItemRecord {
        $record = $existingRecord ?? new ContentItemRecord();

        $record->id = $aggregate->id()->toString();
        $record->ownerId = $aggregate->ownerId()->toString();
        $record->kind = $aggregate->kind()->value;
        $record->title = $aggregate->title()->toString();
        $record->body = $aggregate->body()?->toString();
        $record->scope = $aggregate->scope()->value;
        $record->lifecycleStatus = $aggregate->lifecycleStatus()->value;
        $record->canonicalStatus = $aggregate->canonicalStatus()->value;
        $record->publicationPolicyRequiresApproval = $aggregate->publicationPolicy()->requiresApproval();
        $record->publicationPolicyAllowPersonal = $aggregate->publicationPolicy()->allowsScope(SharingScope::PERSONAL);
        $record->publicationPolicyAllowSharedGroup = $aggregate->publicationPolicy()->allowsScope(SharingScope::SHARED_GROUP);
        $record->publicationPolicyAllowInstitutionManaged = $aggregate->publicationPolicy()->allowsScope(SharingScope::INSTITUTION_MANAGED);
        $record->revisionNumber = $aggregate->revisionNumber()->toInt();
        $record->supersededByContentItemId = $aggregate->supersession()?->successorId()->toString();
        $record->approvedAt = $aggregate->approvedAt();
        $record->blockedAt = $aggregate->blockedAt();
        $record->archivedAt = $aggregate->archivedAt();

        $now = new \DateTimeImmutable();
        if (!isset($record->createdAt)) {
            $record->createdAt = $now;
        }
        $record->supersededAt = $now;

        $record->provenanceSources = new ArrayCollection();
        foreach ($aggregate->provenance()->sources() as $source) {
            $row = new ContentProvenanceSourceRecord();
            $row->contentItem = $record;
            $row->derivationMethod = $source->method()->value;
            $row->recordedAt = $source->recordedAt();

            $rawSource = $source->source();
            if ($rawSource instanceof ContentItemId) {
                $row->sourceContentItemId = $rawSource->toString();
            } else {
                $row->sourceReferenceType = $rawSource->type();
                $row->sourceReferenceValue = $rawSource->value();
            }

            $record->provenanceSources->add($row);
        }

        $record->relationships = new ArrayCollection();
        foreach ($aggregate->relationships() as $relationship) {
            $row = new ContentRelationshipRecord();
            $row->contentItem = $record;
            $row->relationshipType = $relationship->type()->value;
            $row->targetContentItemId = $relationship->targetId()->toString();
            $row->recordedAt = $relationship->recordedAt();

            $record->relationships->add($row);
        }

        $record->reviews = new ArrayCollection();
        foreach ($aggregate->reviewHistory() as $review) {
            $row = new ContentReviewRecord();
            $row->contentItem = $record;
            $row->reviewerId = $review->reviewerId();
            $row->outcome = $review->outcome()->value;
            $row->reason = $review->reason()->toString();
            $row->reviewedAt = $review->reviewedAt();

            $record->reviews->add($row);
        }

        $record->publications = new ArrayCollection();
        foreach ($aggregate->publicationHistory() as $publication) {
            $row = new ContentPublicationHistoryRecord();
            $row->contentItem = $record;
            $row->channel = $publication->channel()->value;
            $row->scope = $publication->scope()->value;
            $row->publishedBy = $publication->publishedBy();
            $row->publishedAt = $publication->publishedAt();

            $record->publications->add($row);
        }

        return $record;
    }
}
