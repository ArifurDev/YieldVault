<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralSetting extends Model
{
    protected $fillable = [
        'referral_xp',
        'referral_amount',
    ];

    protected $casts = [
        'referral_xp'     => 'integer',
        'referral_amount'=> 'decimal:2',
    ];

    /**
     * Always get single row settings
     */
    public static function current(): self
    {
        return self::first() ?? self::create([
            'referral_xp' => 0,
            'referral_amount' => 0,
        ]);
    }
}
