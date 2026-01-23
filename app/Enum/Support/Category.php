<?php

namespace App\Enum\Support;

enum Category : string
{
    case DEPOSIT     = 'deposit';
    case WITHDRAWAL  = 'withdrawal';
    case INVESTMENT  = 'investment';
    case WALLET      = 'wallet';
    case KYC         = 'kyc';
    case TASK        = 'task';
    case REFERRAL    = 'referral';
    case ACCOUNT     = 'account';
    case TECHNICAL   = 'technical';
    case OTHER       = 'other';

    /**
     * Translation key helper
     */
    public function labelKey(): string
    {
        return 'support_category_' . $this->value;
    }

    /**
     * Default English label (fallback)
     */
    public function defaultLabel(): string
    {
        return match ($this) {
            self::DEPOSIT    => 'Deposit',
            self::WITHDRAWAL => 'Withdrawal',
            self::INVESTMENT => 'Investment',
            self::WALLET     => 'Wallet',
            self::KYC        => 'KYC Verification',
            self::TASK       => 'Tasks',
            self::REFERRAL   => 'Referral',
            self::ACCOUNT    => 'Account',
            self::TECHNICAL  => 'Technical Issue',
            self::OTHER      => 'Other',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT    => 'Deposit',
            self::WITHDRAWAL => 'Withdrawal',
            self::INVESTMENT => 'Investment',
            self::WALLET     => 'Wallet',
            self::KYC        => 'KYC Verification',
            self::TASK       => 'Tasks',
            self::REFERRAL   => 'Referral',
            self::ACCOUNT    => 'Account',
            self::TECHNICAL  => 'Technical Issue',
            self::OTHER      => 'Other',
        };
    }
}
