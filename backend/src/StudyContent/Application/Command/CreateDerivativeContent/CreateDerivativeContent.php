<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Command\CreateDerivativeContent;

use App\StudyContent\Domain\Model\ContentItem\ContentKind;
use App\StudyContent\Domain\Model\ContentItem\DerivationMethod;
use App\StudyContent\Domain\Model\ContentItem\SharingScope;

final readonly class CreateDerivativeContent
{
    /**
     * @param list<string> $sourceIds
     */
    public function __construct(
        public string           $ownerId,
        public ContentKind      $kind,
        public string           $title,
        public string           $body,
        public SharingScope     $scope,
        public array            $sourceIds,
        public DerivationMethod $derivationMethod,
    )
    {
    }
}
