<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'level_id',
        'name',
        'price',
        'duration_days',
        'is_active',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'level_id'      => 'integer',
        'price'         => 'decimal:2',
        'duration_days' => 'integer',
        'is_active'     => 'boolean',
    ];

    /**
     * Relationships
     */
    public function level()
    {
        return $this->belongsTo(Level::class);
    }
}
