<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportRequest extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'status',
        'params',
        'file_path',
        'error_message',
        'completed_at'
    ];

    protected $casts = [
        'params' => 'array',
        'completed_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
