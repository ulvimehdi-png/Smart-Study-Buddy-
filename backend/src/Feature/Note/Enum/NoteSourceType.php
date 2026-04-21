<?php

namespace App\Feature\Note\Enum;

enum NoteSourceType: string
{
    case MANUAL = 'manual';
    case UPLOAD = 'upload';
    case IMPORT = 'import';
    case LINK = 'link';

    public static function default(): self
    {
        return self::MANUAL;
    }
}
