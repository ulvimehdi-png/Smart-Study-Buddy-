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
}
