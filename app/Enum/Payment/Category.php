<?php

namespace App\Enum\Payment;

enum Category : string
{
    case DEPOSIT    = 'deposit';
    case WITHDRAWAL = 'withdrawal';

    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT    => 'Deposit',
            self::WITHDRAWAL => 'Withdrawal',
        };
    }
}
