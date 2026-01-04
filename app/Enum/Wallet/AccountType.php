<?php

namespace App\Enum\Wallet;

enum AccountType: string
{
    case BANK   = 'bank';
    case MOBILE = 'mobile';
    case CRYPTO = 'crypto'; // future ready
}
