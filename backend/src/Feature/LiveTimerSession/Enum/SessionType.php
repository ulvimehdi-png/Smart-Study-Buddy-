<?php

namespace App\Feature\LiveTimerSession\Enum;

enum SessionType: string
{
    case MANUAL = 'manual';
    case POMODORO = 'pomodoro';
    case REVIEW = 'review';
    case READING = 'reading';
    case PRACTICE = 'practice';

    public static function default(): self
    {
        return self::MANUAL;
    }
}
