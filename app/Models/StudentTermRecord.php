<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentTermRecord extends Model
{
    protected $fillable = [
        'student_id',
        'term_id',
        'academic_year_id',
        'total_attendance_days',
        'days_absent',
        'conduct_grade',
        'homeroom_teacher_comment',
        'principal_comment',
        'behavior_traits',
    ];

    protected $casts = [
        'behavior_traits' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
