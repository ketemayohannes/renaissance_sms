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
        'email',
        'po_box',
        'logo_path',
        'template_config',
        'yearly_config',
        'grade_scales',
    ];

    protected $casts = [
        'template_config' => 'array',
        'yearly_config' => 'array',
        'grade_scales' => 'array',
    ];
}
