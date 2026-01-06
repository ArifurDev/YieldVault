<?php

namespace App\Enum\ActivityLog;

enum Action : string
{
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case PAID     = 'paid';
    case FLAGGED  = 'flagged';
    case REGISTERED = 'registered';
    case LOGIN     = 'login';
}
