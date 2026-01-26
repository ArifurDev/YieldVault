<?php

namespace App\Enum\Payment;

enum IssueType : string
{
    case DELAY     = 'delay';
    case FAILED    = 'failed';
    case MISMATCH  = 'mismatch';

    public function label(): string
    {
        return match ($this) {
            self::DELAY    => 'Delay',
            self::FAILED   => 'Failed',
            self::MISMATCH => 'Amount Mismatch',
        };
    }
}
