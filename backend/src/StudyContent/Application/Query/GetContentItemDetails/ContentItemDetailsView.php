<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Query\GetContentItemDetails;

final readonly class ContentItemDetailsView
{
    /**
     * @param list<array{type:string,targetId:string}> $relationships
     * @param list<array{outcome:string, reviewerId:string, reviewedAt:string}> $reviews
     */
    public function __construct(
        public string  $id,
        public string  $kind,
        public string  $title,
        public ?string $body,
        public string  $lifecycleStatus,
        public string  $scope,
        public string  $canonicalStatus,
        public array   $relationships,
        public array   $reviews,
    )
    {
    }
}
