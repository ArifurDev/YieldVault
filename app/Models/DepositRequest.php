<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositRequest extends Model
{
    protected $fillable = [
        'user_id',
        'manager_id',
        'amount',
        'status',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /* ========================= Relationships ========================= */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
