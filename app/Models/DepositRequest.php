<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositRequest extends Model
{
    protected $fillable = [
        'user_id',
        'manager_id',
        'amount',
        'wallet_number',
        'proof_path',
        'status',
        'reject_reason',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /* ========================= Relationships ========================= */

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function manager(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
