<?php

declare(strict_types=1);

namespace App\Enums;

enum TaskStatus: string
{
    case COMPLETED     = 'completed';
    case NOT_COMPLETED = 'not_completed';
}
