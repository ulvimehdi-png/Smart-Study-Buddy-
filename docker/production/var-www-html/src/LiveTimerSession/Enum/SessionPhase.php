<?php

namespace App\LiveTimerSession\Enum;

enum SessionPhase: string
{
    case FOCUS = 'focus';
    case BREAK = 'break';

    public static function default(): self
    {
        return self::FOCUS;
    }

    public static function values(): array
    {
        return array_map(static fn(self $phase) => $phase->value, self::cases());
    }
}
