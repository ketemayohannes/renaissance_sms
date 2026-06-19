<?php

namespace App\Models;

use App\Services\GradeCacheService;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\HasDivisionRestriction;

class StudentMark extends Model
{
    use HasFactory, Auditable, HasDivisionRestriction;

    protected static function booted(): void
    {
        $bust = function (self $mark): void {
            if ($mark->section_id && $mark->academic_year_id) {
                app(GradeCacheService::class)->clearSectionCache(
                    (int) $mark->section_id,
                    (int) $mark->academic_year_id
                );
            }
        };

        static::saved($bust);
        static::deleted($bust);
    }

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'term_id',
        'subject_id',
        'assessment_template_id',
        'section_id',
        'teacher_id',
        'score',
        'remarks',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function assessmentTemplate(): BelongsTo
    {
        return $this->belongsTo(AssessmentTemplate::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
