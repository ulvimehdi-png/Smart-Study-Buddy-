<?php

namespace App\Feature\Task\Enum;

enum TaskPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public static function default(): self
    {
        return self::MEDIUM;
    }
}
