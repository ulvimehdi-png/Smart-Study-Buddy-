<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

enum SharingScope: string
{
    case PERSONAL = 'personal';
    case SHARED_GROUP = 'shared_group';
    case INSTITUTION_MANAGED = 'institution_managed';
}
