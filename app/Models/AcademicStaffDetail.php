<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicStaffDetail extends Model
{
    protected $fillable = [
        'employee_id',

        'qualification_level',
        'specialization',

        'secondary_responsibilities',
        'institution',
        'graduation_year',
        'last_degree',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
