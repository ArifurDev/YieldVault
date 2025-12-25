<?php

namespace App\Enum\Wallet;

enum TransactionType : string
{
    case CREDIT = 'credit';
    case DEBIT  = 'debit';
}
