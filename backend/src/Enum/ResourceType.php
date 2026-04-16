<?php

namespace App\Enum;

enum ResourceType: string
{
    case LINK = 'link';
    case FILE = 'file';
    case BOOK = 'book';
    case VIDEO = 'video';
    case ARTICLE = 'article';
    case OTHER = 'other';
}
