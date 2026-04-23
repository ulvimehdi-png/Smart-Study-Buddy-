<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

use App\StudyContent\Domain\Event\ContentApproved;
use App\StudyContent\Domain\Event\ContentArchived;
use App\StudyContent\Domain\Event\ContentBlocked;
use App\StudyContent\Domain\Event\ContentDeprecated;
use App\StudyContent\Domain\Event\ContentImported;
use App\StudyContent\Domain\Event\ContentProcessed;
use App\StudyContent\Domain\Event\ContentPublished;
use App\StudyContent\Domain\Event\ContentRejected;
use App\StudyContent\Domain\Event\ContentSubmittedForReview;
use App\StudyContent\Domain\Event\RelationshipDeclared;
use App\StudyContent\Domain\Exception\DomainException;
use App\StudyContent\Domain\Exception\InvalidLifeCycleTransition;
use App\StudyContent\Domain\Exception\InvalidRelationship;
use App\StudyContent\Domain\Exception\PublicationNotAllowed;
use App\StudyContent\Domain\Exception\ReviewRequired;
use App\StudyContent\Domain\Shared\RecordsDomainEvents;
use DateTimeImmutable;

final class ContentItem
{
    use RecordsDomainEvents;

    private ContentItemId $id;
    private ContentKind $kind;
    private ContentTitle $title;
    private ?ContentBody $body;
    private OwnerId $ownerId;
    private SharingScope $scope;
    private LifecycleStatus $lifecycleStatus;
    private CanonicalStatus $canonicalStatus;
    private Provenance $provenance;
    private PublicationPolicy $publicationPolicy;
    private RevisionNumber $revisionNumber;
    private ?Supersession $supersession = null;
    private ?DateTimeImmutable $approvedAt = null;
    private ?DateTimeImmutable $blockedAt = null;
    private ?DateTimeImmutable $archivedAt = null;
    /** @var list<ReviewRecord> */
    private array $reviewHistory = [];
    /** @var list<PublicationRecord> */
    private array $publicationHistory = [];
    /** @var list<Relationship> */
    private array $relationships = [];

    private function __construct(
        ContentItemId     $id,
        ContentKind       $kind,
        ContentTitle      $title,
        ?ContentBody      $body,
        OwnerId           $ownerId,
        SharingScope      $scope,
        LifecycleStatus   $lifecycleStatus,
        CanonicalStatus   $canonicalStatus,
        Provenance        $provenance,
        PublicationPolicy $publicationPolicy,
        RevisionNumber    $revisionNumber,
    )
    {
        $this->id = $id;
        $this->kind = $kind;
        $this->title = $title;
        $this->body = $body;
        $this->ownerId = $ownerId;
        $this->scope = $scope;
        $this->lifecycleStatus = $lifecycleStatus;
        $this->canonicalStatus = $canonicalStatus;
        $this->provenance = $provenance;
        $this->publicationPolicy = $publicationPolicy;
        $this->revisionNumber = $revisionNumber;
    }

    public static function importSourceMaterial(
        ContentItemId      $id,
        OwnerId            $ownerId,
        ContentTitle       $title,
        SharingScope       $scope,
        SourceReference    $origin,
        ?ContentBody       $body,
        ?PublicationPolicy $publicationPolicy = null,
        ?DateTimeImmutable $now = null,
    ): self
    {
        $now ??= new DateTimeImmutable();

        $self = new self(id: $id,
            kind: ContentKind::SOURCE_DOCUMENT,
            title: $title,
            body: $body,
            ownerId: $ownerId,
            scope: $scope,
            lifecycleStatus: LifecycleStatus::IMPORTED,
            canonicalStatus: CanonicalStatus::NONE,
            provenance: Provenance::imported(ProvenanceSource::importedFrom($origin,
                $now),),
            publicationPolicy: $publicationPolicy
            ??
            PublicationPolicy::standard(),
            revisionNumber: RevisionNumber::first(),);

        $self->record(new ContentImported($self->id, $now,));

        return $self;
    }

    public static function createDraft(
        ContentItemId      $id,
        OwnerId            $ownerId,
        ContentKind        $kind,
        ContentTitle       $title,
        ContentBody        $body,
        SharingScope       $scope,
        ?PublicationPolicy $publicationPolicy = null,
        ?DateTimeImmutable $now = null,
    ): self
    {
        $now ??= new DateTimeImmutable();

        return new self(id: $id,
            kind: $kind,
            title: $title,
            body: $body,
            ownerId: $ownerId,
            scope: $scope,
            lifecycleStatus: LifecycleStatus::DRAFT,
            canonicalStatus: CanonicalStatus::NONE,
            provenance: Provenance::manuallyAuthored($now),
            publicationPolicy: $publicationPolicy
            ??
            PublicationPolicy::standard(),
            revisionNumber: RevisionNumber::first(),);
    }

    /**
     * @param list<ContentItemId> $sourceIds
     */
    public static function createDerivative(
        ContentItemId      $id,
        OwnerId            $ownerId,
        ContentKind        $kind,
        ContentTitle       $title,
        ContentBody        $body,
        SharingScope       $scope,
        array              $sourceIds,
        DerivationMethod   $derivationMethod,
        ?PublicationPolicy $publicationPolicy = null,
        ?DateTimeImmutable $now = null,
    ): self
    {
        $now ??= new DateTimeImmutable();

        $sources = array_map(static fn(
            ContentItemId $sourceId,
        ): ProvenanceSource => ProvenanceSource::derivedFrom($sourceId, $derivationMethod, $now),
            $sourceIds,);

        $self = new self(id: $id,
            kind: $kind,
            title: $title,
            body: $body,
            ownerId: $ownerId,
            scope: $scope,
            lifecycleStatus: LifecycleStatus::DRAFT,
            canonicalStatus: CanonicalStatus::NONE,
            Provenance: Provenance::derived($sources),
            publicationPolicy: $publicationPolicy
            ??
            PublicationPolicy::standard(),
            revisionNumber: RevisionNumber::first(),);

        foreach ($sourceIds as $sourceId) {
            $self->relationships[] = new Relationship(RelationshipType::DERIVES_FROM,
                $sourceId, $now,);
        }

        return $self;
    }

    public function markProcessed(
        ?ContentBody       $body = null,
        ?DateTimeImmutable $now = null,
    ): void
    {
        $now ??= new DateTimeImmutable();

        if ($this->lifecycleStatus !== LifecycleStatus::IMPORTED) {
            throw InvalidLifecycleTransition::fromTo($this->lifecycleStatus,
                'markProcessed',);
        }

        if ($body !== null) {
            $this->body = $body;
        }

        $this->lifecycleStatus = LifecycleStatus::PROCESSED;
        $this->revisionNumber = $this->revisionNumber->next();

        $this->record(new ContentProcessed($this->id, $now,));
    }

    public function submitForReview(?DateTimeImmutable $now = null): void
    {
        $now ??= new DateTimeImmutable();

        if (!in_array($this->lifecycleStatus,
            [LifecycleStatus::DRAFT, LifecycleStatus::PROCESSED,], true,)) {
            throw InvalidLifecycleTransition::fromTo($this->lifecycleStatus,
                'submitForReview',);
        }

        $this->lifecycleStatus = LifecycleStatus::IN_REVIEW;

        $this->record(new ContentSubmittedForReview($this->id, $now,));
    }

    public function approve(
        string               $reviewerId,
        PolicyDecisionReason $reason,
        ?DateTimeImmutable   $now = null,
    ): void
    {
        $now ??= new DateTimeImmutable();

        if ($this->lifecycleStatus !== LifecycleStatus::IN_REVIEW) {
            throw InvalidLifecycleTransition::fromTo($this->lifecycleStatus, 'approve');
        }

        $this->reviewHistory[] = new ReviewRecord(reviewerId: $reviewerId,
            outcome: ReviewOutcome::APPROVED,
            reason: $reason, reviewedAt: $now,);

        $this->lifecycleStatus = LifecycleStatus::APPROVED;
        $this->approvedAt = $now;

        $this->record(new ContentApproved($this->id, $reviewerId, $now,));
    }

    public function reject(
        string               $reviewerId,
        PolicyDecisionReason $reason,
        ?DateTimeImmutable   $now = null,
    ): void
    {
        $now ??= new DateTimeImmutable();

        if ($this->lifecycleStatus !== LifecycleStatus::IN_REVIEW) {
            throw InvalidLifecycleTransition::fromTo($this->lifecycleStatus, 'reject');
        }

        $this->reviewHistory[] = new ReviewRecord(reviewerId: $reviewerId,
            outcome: ReviewOutcome::REJECTED,
            reason: $reason, reviewedAt: $now,);

        $this->lifecycleStatus = LifecycleStatus::DRAFT;
        $this->revisionNumber = $this->revisionNumber->next();

        $this->record(new ContentRejected($this->id, $reviewerId, $now,));
    }

    public function publish(
        PublicationChannel $channel,
        string             $publishedBy,
        ?DateTimeImmutable $now = null,
    ): void
    {
        $now ??= new DateTimeImmutable();

        if ($this->lifecycleStatus === LifecycleStatus::BLOCKED) {
            throw PublicationNotAllowed::blocked();
        }

        if ($this->lifecycleStatus === LifecycleStatus::ARCHIVED) {
            throw PublicationNotAllowed::archived();
        }

        if ($this->lifecycleStatus === LifecycleStatus::DEPRECATED) {
            throw PublicationNotAllowed::deprecated();
        }

        if ($this->publicationPolicy->requiresApproval()
            && $this->lifecycleStatus !== LifecycleStatus::APPROVED) {
            throw ReviewRequired::beforePublication();
        }

        if (!$this->publicationPolicy->allowsScope($this->scope)) {
            throw PublicationNotAllowed::unsupportedScope($this->scope->value);
        }

        $this->publicationHistory[] = new PublicationRecord(channel: $channel,
            scope: $this->scope,
            publishedBy: $publishedBy,
            publishedAt: $now,);

        $this->lifecycleStatus = LifecycleStatus::PUBLISHED;

        $this->record(new ContentPublished($this->id, $channel, $now,));
    }

    public function block(
        PolicyDecisionReason $reason,
        ?DateTimeImmutable   $now = null,
    ): void
    {
        $now ??= new DateTimeImmutable();

        if ($this->lifecycleStatus === LifecycleStatus::ARCHIVED) {
            throw InvalidLifecycleTransition::fromTo($this->lifecycleStatus, 'block');
        }

        $this->reviewHistory[] = new ReviewRecord(reviewerId: 'system',
            outcome: ReviewOutcome::BLOCKED,
            reason: $reason, reviewedAt: $now,);

        $this->lifecycleStatus = LifecycleStatus::BLOCKED;
        $this->blockedAt = $now;

        $this->record(new ContentBlocked($this->id, $now,));
    }

    public function deprecate(?DateTimeImmutable $now = null): void
    {
        $now ??= new DateTimeImmutable();

        if (!in_array($this->lifecycleStatus,
            [LifecycleStatus::APPROVED, LifecycleStatus::PUBLISHED,], true,)) {
            throw InvalidLifecycleTransition::fromTo($this->lifecycleStatus,
                'deprecate',);
        }

        $this->lifecycleStatus = LifecycleStatus::DEPRECATED;

        $this->record(new ContentDeprecated($this->id, $now,));
    }

    public function archive(?DateTimeImmutable $now = null): void
    {
        $now ??= new DateTimeImmutable();

        if ($this->lifecycleStatus === LifecycleStatus::BLOCKED) {
            throw InvalidLifecycleTransition::fromTo($this->lifecycleStatus, 'archive');
        }

        $this->lifecycleStatus = LifecycleStatus::ARCHIVED;
        $this->archivedAt = $now;

        $this->record(new ContentArchived($this->id, $now,));
    }

    public function markCanonical(): void
    {
        if ($this->lifecycleStatus === LifecycleStatus::BLOCKED) {
            throw new InvalidLifeCycleTransition('Blocked content cannot become canonical.',);
        }

        $this->canonicalStatus = CanonicalStatus::CANONICAL;
    }

    public function markDuplicateOfCanonical(
        ContentItemId      $canonicalId,
        ?DateTimeImmutable $now = null,
    ): void
    {
        $this->canonicalStatus = CanonicalStatus::DUPLICATE_OF_CANONICAL;
        $this->addRelationship(RelationshipType::DUPLICATES, $canonicalId, $now);
    }

    public function addRelationship(
        RelationshipType   $type,
        ContentItemId      $targetId,
        ?DateTimeImmutable $now = null,
    ): void
    {
        $now ??= new DateTimeImmutable();

        if ($type->forbidsSelfReference() && $this->id->equals($targetId)) {
            throw InvalidRelationship::selfReference($type);
        }

        $candidate = new Relationship($type, $targetId, $now,);

        foreach ($this->relationships as $existing) {
            if ($existing->sameAs($candidate)) {
                throw InvalidRelationship::duplicate($type, $targetId->toString());
            }
        }

        $this->relationships[] = $candidate;

        $this->record(new RelationshipDeclared($this->id, $type, $targetId, $now,));
    }

    //    public function supersedeWith(ContentItemId $successorId, ?DateTimeImmutable $now = null): void
    //    {
    //        $now ??= new DateTimeImmutable();
    //
    //        if ($this->id->equals($successorId)) {
    //            throw SupersessionConflict::selfSupersession();
    //        }
    //
    //        if ($this->supersession !== null) {
    //            throw SupersessionConflict::alreadySuperseded();
    //        }
    //
    //        $this->supersession = new Supersession($successorId, $now);
    //        $this->lifecycleStatus = LifecycleStatus::DEPRECATED;
    //
    //        $this->relationships[] = new Relationship(
    //            RelationshipType::SUPERSEDES,
    //            $successorId,
    //            $now
    //        );
    //
    //        $this->record(new ContentSuperseded($this->id, $successorId, $now));
    //    }

    public function revise(
        ContentTitle $title,
        ?ContentBody $body = null,
    ): void
    {
        if (in_array($this->lifecycleStatus,
            [LifecycleStatus::ARCHIVED, LifecycleStatus::BLOCKED,], true,)) {
            throw InvalidLifecycleTransition::fromTo($this->lifecycleStatus, 'revise');
        }

        $this->title = $title;
        $this->body = $body ?? $this->body;
        $this->revisionNumber = $this->revisionNumber->next();

        if ($this->lifecycleStatus === LifecycleStatus::APPROVED
            || $this->lifecycleStatus === LifecycleStatus::PUBLISHED) {
            $this->lifecycleStatus = LifecycleStatus::DRAFT;
        }
    }

    /**
     * @param list<ReviewRecord> $reviewHistory
     * @param list<PublicationRecord> $publicationHistory
     * @param list<Relationship> $relationships
     */
    public static function reconstitute(
        ContentItemId       $id,
        ContentKind         $kind,
        ContentTitle        $title,
        ?ContentBody        $body,
        OwnerId             $ownerId,
        SharingScope        $scope,
        LifecycleStatus     $lifecycleStatus,
        CanonicalStatus     $canonicalStatus,
        Provenance          $provenance,
        PublicationPolicy   $publicationPolicy,
        RevisionNumber      $revisionNumber,
        ?Supersession       $supersession,
        ?\DateTimeImmutable $approvedAt,
        ?\DateTimeImmutable $blockedAt,
        ?\DateTimeImmutable $archivedAt,
        array               $reviewHistory,
        array               $publicationHistory,
        array               $relationships
    ): self {
        $self = new self(
            id: $id,
            kind: $kind,
            title: $title,
            body: $body,
            ownerId: $ownerId,
            scope: $scope,
            lifecycleStatus: $lifecycleStatus,
            canonicalStatus: $canonicalStatus,
            Provenance: $provenance,
            publicationPolicy: $publicationPolicy,
            revisionNumber: $revisionNumber
        );

        $self->supersession = $supersession;
        $self->approvedAt = $approvedAt;
        $self->blockedAt = $blockedAt;
        $self->archivedAt = $archivedAt;
        $self->reviewHistory = $reviewHistory;
        $self->publicationHistory = $publicationHistory;
        $self->relationships = $relationships;

        return $self;
    }

    public function id(): ContentItemId
    {
        return $this->id;
    }

    public function kind(): ContentKind
    {
        return $this->kind;
    }

    public function title(): ContentTitle
    {
        return $this->title;
    }

    public function body(): ?ContentBody
    {
        return $this->body;
    }

    public function ownerId(): OwnerId
    {
        return $this->ownerId;
    }

    public function scope(): SharingScope
    {
        return $this->scope;
    }

    public function lifecycleStatus(): LifecycleStatus
    {
        return $this->lifecycleStatus;
    }

    public function canonicalStatus(): CanonicalStatus
    {
        return $this->canonicalStatus;
    }

    public function provenance(): Provenance
    {
        return $this->provenance;
    }

    public function supersession(): ?Supersession
    {
        return $this->supersession;
    }

    public function revisionNumber(): RevisionNumber
    {
        return $this->revisionNumber;
    }

    /**
     * @return list<ReviewRecord>
     */
    public function reviewHistory(): array
    {
        return $this->reviewHistory;
    }

    /**
     * @return list<PublicationRecord>
     */
    public function publicationHistory(): array
    {
        return $this->publicationHistory;
    }

    /**
     * @return list<Relationship>
     */
    public function relationships(): array
    {
        return $this->relationships;
    }

    public function isPublishable(): bool
    {
        return $this->lifecycleStatus === LifecycleStatus::APPROVED
            && $this->canonicalStatus !== CanonicalStatus::DUPLICATE_OF_CANONICAL;
    }

    public function publicationPolicy(): PublicationPolicy
    {
        return $this->publicationPolicy;
    }

    public function approvedAt(): ?\DateTimeImmutable
    {
        return $this->approvedAt;
    }

    public function blockedAt(): ?\DateTimeImmutable
    {
        return $this->blockedAt;
    }

    public function archivedAt(): ?\DateTimeImmutable
    {
        return $this->archivedAt;
    }
}
