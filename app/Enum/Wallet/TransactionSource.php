<?php

namespace App\Enum\Wallet;

enum TransactionSource : string
{
    case ADMIN_FUND = 'admin_fund';
    case DEPOSIT    = 'deposit';
    case WITHDRAW   = 'withdraw';
}
