<?php

namespace App\Feature\Task\Enum;

enum TaskStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';
    case CANCELLED = 'cancelled';

    public static function default(): self
    {
        return self::TODO;
    }
}
