<?php

namespace App\Feature\User\Enum;

enum WeekStartDay: string
{
    case MONDAY = 'monday';
    case SUNDAY = 'sunday';

    public static function default(): self
    {
        return self::MONDAY;
    }
}
