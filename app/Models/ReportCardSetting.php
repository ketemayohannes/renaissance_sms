<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportCardSetting extends Model
{
    protected $fillable = [
        'school_name',
        'school_address',
        'website',
        'telephone',
        'logo_path',
        'template_config',
    ];

    protected $casts = [
        'template_config' => 'array',
    ];
}
