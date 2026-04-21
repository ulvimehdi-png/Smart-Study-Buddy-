<?php

namespace App\Feature\Resource\Enum;

enum ResourceType: string
{
    case LINK = 'link';
    case FILE = 'file';
    case BOOK = 'book';
    case VIDEO = 'video';
    case ARTICLE = 'article';
    case OTHER = 'other';

    public static function default(): self
    {
        return self::OTHER;
    }
}
