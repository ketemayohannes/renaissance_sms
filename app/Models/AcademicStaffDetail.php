<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicStaffDetail extends Model
{
    protected $fillable = [
        'employee_id',
        'teacher_rank',
        'qualification_level',
        'specialization',
        'periods_per_week',
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
