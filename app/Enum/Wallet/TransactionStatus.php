<?php

namespace App\Enum\Wallet;

enum TransactionStatus : string
{
    case PENDING  = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case PAID     = 'paid';
}
