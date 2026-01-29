<?php

namespace App\Enum\Payment;

use App\Helpers\translateHelper;

enum IssueType : string
{
    case DELAY     = 'delay';
    case FAILED    = 'failed';
    case MISMATCH  = 'mismatch';

    public function label(string $lang = 'en'): string
    {
        return translateHelper::translateCached(
            'payment_issue_type_' . $this->value,
            match ($this) {
                self::DELAY    => 'Delay',
                self::FAILED   => 'Failed',
                self::MISMATCH => 'Amount Mismatch',
            },
            $lang
        );
    }
}
