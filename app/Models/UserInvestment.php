<?php

namespace App\Models;

use App\Enum\Investment\Status;
use Illuminate\Database\Eloquent\Model;

class UserInvestment extends Model
{
    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'user_id',
        'package_id',
        'amount',
        'roi_percent',
        'duration_days',
        'start_date',
        'end_date',
        'earned_amount',
        'status',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'user_id'        => 'integer',
        'package_id'     => 'integer',
        'amount'         => 'decimal:2',
        'roi_percent'    => 'decimal:2',
        'duration_days'  => 'integer',
        'start_date'     => 'date',
        'end_date'       => 'date',
        'earned_amount'  => 'decimal:2',
        'status'         => Status::class, // PHP 8.1 Enum casting
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Helper: calculate earned amount
     */
    public function calculateEarnedAmount(): float
    {
        return $this->amount * ($this->roi_percent / 100);
    }
}
