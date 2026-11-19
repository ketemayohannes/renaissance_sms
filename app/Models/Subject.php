<?php

namespace App\Models;

use App\Helpers\CachedData;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
            ->withPivot('sort_order', 'academic_year_id')
            ->withPivotValue('academic_year_id', CachedData::activeAcademicYear()?->id)
            ->withTimestamps();
    }
}
