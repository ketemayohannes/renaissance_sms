<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeComponent extends Model
{
    protected $fillable = [
        'academic_year_id',
        'grade_level_id',
        'subject_id',
        'term_id',
        'assessment_type_id',
        'name',
        'weight',
        'max_score',
        'order',
        'is_active',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'max_score' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function assessmentType()
    {
        return $this->belongsTo(AssessmentType::class);
    }

    public function studentMarks()
    {
        return $this->hasMany(StudentMark::class);
    }

    // Helper method to get weighted score
    public function getWeightedScore($score)
    {
        return ($score / $this->max_score) * $this->weight;
    }
}
