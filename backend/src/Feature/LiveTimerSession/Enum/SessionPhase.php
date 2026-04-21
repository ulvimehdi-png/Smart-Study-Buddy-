<?php

namespace App\Feature\LiveTimerSession\Enum;

enum SessionPhase: string
{
    case FOCUS = 'focus';
    case BREAK = 'break';

    public static function default(): self
    {
        return self::FOCUS;
    }
}
