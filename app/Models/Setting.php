<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'system_title',
        'logo',
        'favicon',
        'copyright_text',
    ];
}
