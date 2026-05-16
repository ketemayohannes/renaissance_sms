<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectAnalysisReport extends Model
{
    protected $fillable = [
        'teacher_assignment_id',
        'term_id',
        'academic_year_id',
        'range_0_49_remark',
        'range_50_74_remark',
        'range_75_100_remark',
        'comparison_comment',
        'section_remark',
        'problems_encountered',
        'solutions_implemented',
        'additional_comment',
    ];

    public function teacherAssignment(): BelongsTo
    {
        return $this->belongsTo(TeacherAssignment::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
