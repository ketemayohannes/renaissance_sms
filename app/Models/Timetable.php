<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    protected $fillable = [
        'academic_year_id',
        'section_id',
        'class_period_id',
        'day_of_week',
        'teacher_assignment_id',
        'room_number',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function classPeriod()
    {
        return $this->belongsTo(ClassPeriod::class);
    }

    public function teacherAssignment()
    {
        return $this->belongsTo(TeacherAssignment::class);
    }
}
