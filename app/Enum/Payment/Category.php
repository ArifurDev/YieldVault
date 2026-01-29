<?php

namespace App\Enum\Payment;

use App\Helpers\translateHelper;

enum Category : string
{
    case DEPOSIT    = 'deposit';
    case WITHDRAWAL = 'withdrawal';

    public function label(string $lang = 'en'): string
    {
        return translateHelper::translateCached(
            'payment_category_' . $this->value,
            match ($this) {
                self::DEPOSIT    => 'Deposit',
                self::WITHDRAWAL => 'Withdrawal',
            },
            $lang
        );
    }
}
