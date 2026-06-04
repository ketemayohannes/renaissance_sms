<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPromotion extends Model
{
    protected $fillable = [
        'student_id',
        'from_academic_year_id',
        'to_academic_year_id',
        'from_grade_level_id',
        'to_grade_level_id',
        'to_section_id',
        'status',
        'is_enrolled',
        'remarks',
        'processed_by',
    ];

    protected $casts = [
        'is_enrolled' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function fromAcademicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'from_academic_year_id');
    }

    public function toAcademicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'to_academic_year_id');
    }

    public function fromGradeLevel()
    {
        return $this->belongsTo(GradeLevel::class, 'from_grade_level_id');
    }

    public function toGradeLevel()
    {
        return $this->belongsTo(GradeLevel::class, 'to_grade_level_id');
    }

    public function toSection()
    {
        return $this->belongsTo(Section::class, 'to_section_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
