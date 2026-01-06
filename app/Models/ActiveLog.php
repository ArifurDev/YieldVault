<?php

namespace App\Models;

use App\Enum\ActivityLog\Action;
use App\Enum\ActivityLog\Type;
use Illuminate\Database\Eloquent\Model;

class ActiveLog extends Model
{
    protected $fillable = [
        'actor_id',
        'target_user_id',
        'type',
        'action',
        'amount',
        'reference_type',
        'reference_id',
        'message',
        'level',
    ];

    protected $casts = [
        'type'   => Type::class,
        'action' => Action::class,
        'level'  => \App\Enum\ActivityLog\Level::class,
    ];
}
