<?php

namespace App\Models;

use App\Helpers\CachedData;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Subject extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'department_id',
        'is_active',
        'is_elective',
        'sort_order',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    protected $casts = [
        'is_active' => 'boolean',
        'is_elective' => 'boolean',
    ];

    // Grade levels that teach this subject in the active academic year
    // (assignments are kept per year).
    public function gradeLevels()
    {
        return $this->belongsToMany(GradeLevel::class, 'grade_level_subjects')
            ->withPivot('sort_order', 'academic_year_id', 'is_elective')
            ->withPivotValue('academic_year_id', CachedData::activeAcademicYear()?->id)
            ->withTimestamps();
    }

    /** Elective setting for one grade and year, set by forGrade(); null = not set. */
    protected ?bool $gradeElectiveOverride = null;

    /**
     * Whether this subject is an elective. A grade's assignment for a year can override
     * the subject's own setting (grade_level_subjects.is_elective), so the same subject can
     * be an elective in one year and a regular subject in the next. The override is used
     * when the subject was loaded through a grade's subject list (pivot) or after
     * forGrade(); otherwise the subject's own setting applies.
     */
    public function getIsElectiveAttribute($value): bool
    {
        if ($this->gradeElectiveOverride !== null) {
            return $this->gradeElectiveOverride;
        }

        $pivot = $this->relations['pivot'] ?? null;
        if ($pivot && isset($pivot->is_elective)) {
            return (bool) $pivot->is_elective;
        }

        return (bool) $value;
    }

    /** The subject's own elective setting, ignoring any grade/year override. */
    public function defaultIsElective(): bool
    {
        return (bool) ($this->attributes['is_elective'] ?? false);
    }

    /**
     * Apply the grade + year elective setting to this instance, for code that loaded the
     * subject on its own (e.g. from a teacher assignment) rather than via the grade.
     */
    public function forGrade(?int $gradeLevelId, ?int $academicYearId): static
    {
        $value = ($gradeLevelId && $academicYearId)
            ? DB::table('grade_level_subjects')
                ->where('grade_level_id', $gradeLevelId)
                ->where('subject_id', $this->id)
                ->where('academic_year_id', $academicYearId)
                ->value('is_elective')
            : null;

        $this->gradeElectiveOverride = $value === null ? null : (bool) $value;

        return $this;
    }
}
