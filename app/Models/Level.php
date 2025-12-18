<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'name',
        'slug',
        'required_xp',
        'daily_roi',
        'icon',
        'order',
        'is_active',
        'is_coming_soon',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'required_xp'     => 'integer',
        'daily_roi'       => 'float',
        'order'           => 'integer',
        'is_active'       => 'boolean',
        'is_coming_soon'  => 'boolean',
    ];
}
