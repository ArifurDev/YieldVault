<?php

namespace App\Enum;

enum Role : string
{
    case ADMIN      = 'admin';
    case USER       = 'user';
    case MODERATOR  = 'moderator';
    case SUPER_ADMIN = 'super_admin';
}
