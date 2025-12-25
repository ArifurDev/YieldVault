<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{

    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'amount',
        'type',
        'source',
        'status',
        'reference_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /* ========================= Relationships ========================= */

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
