<?php

namespace App\Enum\Support;

enum Status : string
{
    case OPEN    = 'open';
    case PENDING = 'pending';
    case CLOSED  = 'closed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
