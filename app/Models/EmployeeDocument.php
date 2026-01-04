<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'name',
        'file_path',
        'file_extension',
        'file_size',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getUrlAttribute()
    {
        return Storage::url($this->file_path);
    }
}
