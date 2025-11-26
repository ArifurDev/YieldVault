<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralLink extends Model
{
    protected $fillable = ['user_id','ref_code','unique_token','expires_at','meta'];

    protected $casts = [
        'meta' => 'array',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired()
    {
        return now()->greaterThan($this->expires_at);
    }
}
