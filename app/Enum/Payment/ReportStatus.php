<?php

namespace App\Enum\Payment;

enum ReportStatus : string
{
    case OPEN        = 'open';
    case IN_PROGRESS = 'in_progress';
    case RESOLVED    = 'resolved';
    case REJECTED    = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::OPEN        => 'Open',
            self::IN_PROGRESS => 'In Progress',
            self::RESOLVED    => 'Resolved',
            self::REJECTED    => 'Rejected',
        };
    }
}
