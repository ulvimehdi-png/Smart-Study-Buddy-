<?php

namespace App\LiveTimerSession\Enum;

enum SessionStatus: string
{
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case RUNNING = 'running';
    case IDLE = 'idle';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public static function default(): self
    {
        return self::ACTIVE;
    }

    public static function shareableValues(): array
    {
        return [
            self::IDLE->value,
            self::PAUSED->value,
            self::RUNNING->value,
        ];
    }
}
