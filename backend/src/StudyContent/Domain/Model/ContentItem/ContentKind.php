<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

enum ContentKind: string
{
    case SOURCE_DOCUMENT = 'source_document';
    case EXTRACTED_TEXT = 'extracted_text';
    case NOTE = 'note';
    case SUMMARY = 'summary';
    case FLASHCARD_SET = 'flashcard_set';
    case CURATED_DIGEST = 'curated_digest';
}
