<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassPeriod extends Model
{
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'is_break',
        'sort_order',
    ];

    protected $casts = [
        'is_break' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];
}
