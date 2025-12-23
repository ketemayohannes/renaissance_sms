<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionRule extends Model
{
    protected $fillable = [
        'from_grade_level_id',
        'to_grade_level_id',
        'academic_year_id',
        'min_average',
        'min_attendance_percent',
        'max_failed_subjects',
        'description',
    ];

    protected $casts = [
        'min_average' => 'decimal:2',
        'min_attendance_percent' => 'decimal:2',
        'max_failed_subjects' => 'integer',
    ];

    public function fromGradeLevel()
    {
        return $this->belongsTo(GradeLevel::class, 'from_grade_level_id');
    }

    public function toGradeLevel()
    {
        return $this->belongsTo(GradeLevel::class, 'to_grade_level_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
