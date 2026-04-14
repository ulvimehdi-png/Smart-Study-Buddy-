<?php

namespace App\Enum;

enum SessionStatus: string
{
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
