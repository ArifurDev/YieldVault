<?php

namespace App\Models;

use App\Enum\Task\MediaType;
use App\Enum\Task\Type;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'title',
        'type',
        'duration',
        'xp',
        'media_type',
        'media_path',
        'is_active',
    ];

    /**
     * Enum casting
     */
    protected $casts = [
        'type'       => Type::class,
        'media_type' => MediaType::class,
        'is_active'  => 'boolean',
    ];

    public function dailyTasks()
    {
        return $this->hasMany(DailyTask::class);
    }
}
