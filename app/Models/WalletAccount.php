<?php

namespace App\Models;

use App\Enum\Wallet\AccountType;
use Illuminate\Database\Eloquent\Model;

class WalletAccount extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'label',
        'account_number',
        'network',
        'is_active',
    ];

    protected $casts = [
        'type'      => AccountType::class,
        'is_active' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
