<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyTaskLog extends Model
{
    protected $fillable = [
        'user_id',
        'daily_task_id',
        'task_id',
        'xp_rewarded',
        'completed_date',
        'streak',
    ];

    protected $casts = [
        'user_id'       => 'integer',
        'daily_task_id' => 'integer',
        'task_id'       => 'integer',
        'xp_rewarded'   => 'integer',

        'completed_date'=> 'date:Y-m-d',
        'streak'        => 'string',
    ];

    /**
     * Relationships.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dailyTask()
    {
        return $this->belongsTo(DailyTask::class, 'daily_task_id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
