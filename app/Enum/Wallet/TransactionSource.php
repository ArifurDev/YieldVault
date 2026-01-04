<?php

namespace App\Enum\Wallet;

enum TransactionSource : string
{
    // Admin / System
    case ADMIN_FUND        = 'admin_fund';

    // User wallet movement
    case DEPOSIT           = 'deposit';
    case WITHDRAW          = 'withdraw';

    // Investment
    case INVESTMENT_BUY    = 'investment_buy';     // user invests money
    case INVESTMENT_PROFIT = 'investment_profit';  // daily / final profit

    // Extra earnings (future-safe)
    case REFERRAL_REWARD   = 'referral_reward';
    case TASK_REWARD       = 'task_reward';
}
