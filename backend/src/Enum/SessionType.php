<?php

namespace App\Enum;

enum SessionType: string
{
    case MANUAL = 'manual';
    case POMODORO = 'pomodoro';
    case REVIEW = 'review';
    case READING = 'reading';
    case PRACTICE = 'practice';
}
