<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentLog extends Model
{
    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'user_investment_id',
        'date',
        'profit',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'user_investment_id' => 'integer',
        'date'               => 'date',
        'profit'             => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function investment()
    {
        return $this->belongsTo(UserInvestment::class, 'user_investment_id');
    }
}
