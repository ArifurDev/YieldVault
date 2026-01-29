<?php

namespace App\Enum\Payment;

use App\Helpers\translateHelper;

enum ReportStatus : string
{
    case OPEN        = 'open';
    case IN_PROGRESS = 'in_progress';
    case RESOLVED    = 'resolved';
    case REJECTED    = 'rejected';

    public function label(string $lang = 'en'): string
    {
        return translateHelper::translateCached(
            'payment_report_status_' . $this->value,
            match ($this) {
                self::OPEN        => 'Open',
                self::IN_PROGRESS => 'In Progress',
                self::RESOLVED    => 'Resolved',
                self::REJECTED    => 'Rejected',
            },
            $lang
        );
    }
}
