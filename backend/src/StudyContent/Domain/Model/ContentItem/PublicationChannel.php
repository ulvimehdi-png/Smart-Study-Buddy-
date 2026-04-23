<?php

declare(strict_types=1);

namespace App\StudyContent\Domain\Model\ContentItem;

enum PublicationChannel: string
{
    case PRIVATE_LIBRARY = 'private_library';
    case GROUP_LIBRARY = 'group_library';
    case INSTITUTION_LIBRARY = 'institution_library';
}
