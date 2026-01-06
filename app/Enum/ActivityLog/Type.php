<?php

namespace App\Enum\ActivityLog;

enum Type : string
{
    case DEPOSIT  = 'deposit';
    case WITHDRAW = 'withdraw';
    case KYC      = 'kyc';
    case LOGIN    = 'login';
    case LOGOUT    = 'logout';
    case REGISTER  = 'register';
}
