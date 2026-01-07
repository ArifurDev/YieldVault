<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositRequest extends Model
{
    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'manager_id',
        'amount',
        'wallet_number',
        'proof_path',
        'status',
        'reject_reason',
        'remarks',

        // new wallet snapshot fields
        'manager_wallet_account_id',
        'wallet_method',
        'wallet_label',
        'wallet_account_number',
        'wallet_provider',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'manager_wallet_account_id' => 'integer',
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

    public function managerWalletAccount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(  WalletAccount::class,'manager_wallet_account_id');
    }

}
