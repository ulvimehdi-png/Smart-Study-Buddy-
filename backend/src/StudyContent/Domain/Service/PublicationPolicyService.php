<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Service;

use App\StudyContent\Domain\Model\ContentItem\ContentItem;
use App\StudyContent\Domain\Model\ContentItem\PublicationChannel;

interface PublicationPolicyService
{
    public function assertContentCanBePublished(
        ContentItem        $contentItem,
        PublicationChannel $channel,
    ): void;

    //    public function assertCollectionCanBePublished(
    //        Collection $collection
    //    ): void;
}
