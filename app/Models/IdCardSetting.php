<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdCardSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_name',
        'logo_path',
        'primary_color',

        'secondary_color',
        'text_color',
        'front_fields',
        'back_fields',
        'back_content',
        'show_barcode',
        'show_qr_code',
        'photo_shape',
    ];

    protected $casts = [
        'front_fields' => 'array',
        'back_fields' => 'array',
        'show_barcode' => 'boolean',
        'show_qr_code' => 'boolean',
    ];
}
