<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AssessmentTemplate extends Model
{
    protected $fillable = [
        'academic_year_id',
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
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function assessmentType(): BelongsTo
    {
        return $this->belongsTo(AssessmentType::class);
    }

    public function gradeLevels(): BelongsToMany
    {
        return $this->belongsToMany(GradeLevel::class, 'assessment_template_assignments')
            ->withTimestamps();
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'assessment_template_assignments')
            ->withTimestamps();
    }

    // Get all assignments (grade/subject combinations)
    public function assignments()
    {
        return $this->hasMany(AssessmentTemplateAssignment::class);
    }

    // Scope to get templates for a specific grade/subject/term
    public function scopeForGradeSubject($query, $gradeLevelId, $subjectId, $termId = null)
    {
        return $query->whereHas('assignments', function ($q) use ($gradeLevelId, $subjectId) {
            $q->where('grade_level_id', $gradeLevelId)
              ->where('subject_id', $subjectId);
        })->when($termId, function ($q) use ($termId) {
            $q->where('term_id', $termId);
        });
    }
}
