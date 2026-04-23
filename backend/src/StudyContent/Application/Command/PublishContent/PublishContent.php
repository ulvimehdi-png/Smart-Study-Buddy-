<?php

declare(strict_types=1);

namespace App\StudyContent\Application\Command\PublishContent;

use App\StudyContent\Domain\Model\ContentItem\PublicationChannel;

final readonly class PublishContent
{
    public function __construct(
        public string             $contentItemId,
        public PublicationChannel $channel,
        public string             $publishedBy,
    )
    {
    }
}
