<?php

namespace App\Models;

use App\Helpers\CachedData;
use App\Traits\Auditable;
use App\Traits\HasDivisionRestriction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    use Auditable, HasDivisionRestriction, HasFactory;

    protected $fillable = ['division_id', 'name', 'code', 'sort_order', 'is_active'];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    /**
     * Subjects assigned to this grade in the active academic year. Assignments are kept
     * per year, so code working on a specific (possibly past) year must use
     * subjectsForYear() instead.
     */
    public function subjects()
    {
        return $this->subjectsForYear(CachedData::activeAcademicYear()?->id);
    }

    /** Subjects assigned to this grade in the given academic year. */
    public function subjectsForYear(?int $academicYearId)
    {
        return $this->belongsToMany(Subject::class, 'grade_level_subjects')
            ->withPivot('is_required', 'sort_order', 'academic_year_id', 'is_elective')
            ->withPivotValue('academic_year_id', $academicYearId)
            ->withTimestamps();
    }
}
