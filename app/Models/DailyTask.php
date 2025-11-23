<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyTask extends Model
{

    protected $fillable = [
        'task_id',
        'date',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'task_id'   => 'integer',
        'sort_order'=> 'integer',
        'is_active' => 'boolean',
        'date'      => 'date:Y-m-d',
    ];


    public function task()
    {
        return $this->belongsTo(Task::class,'task_id');
    }
}
