<?php

namespace App\Enum\ActivityLog;

enum Level: string
{
    case SUCCESS = 'success';
    case DANGER  = 'danger';
    case WARNING = 'warning';
    case INFO    = 'info';
}
