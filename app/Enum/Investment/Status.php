<?php

namespace App\Enum\Investment;

enum Status: string
{
    case ACTIVE  = 'active';
    case COMPLETED = 'completed';
}
