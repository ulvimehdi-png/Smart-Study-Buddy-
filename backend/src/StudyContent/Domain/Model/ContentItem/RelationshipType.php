<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

enum RelationshipType: string
{
    case SUMMARIZES = 'summarizes';
    case EXPANDS = 'expands';
    case REVISES = 'revises';
    case DEPENDS_ON = 'depends_on';
    case DERIVES_FROM = 'derives_from';
    case REFERENCES = 'references';
    //case SUPERSEDES = 'supersedes';
    case DUPLICATES = 'duplicates';

    public function forbidsSelfReference(): bool
    {
        return true;
    }
}
