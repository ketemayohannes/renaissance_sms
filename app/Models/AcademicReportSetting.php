<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicReportSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'roster_logo_path',
        'school_name',
        'display_options',
    ];

    protected $casts = [
        'display_options' => 'array',
    ];
}
