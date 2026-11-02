<?php

namespace App\Models;

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

    // Relationship: Grade levels that teach this subject
    public function gradeLevels()
    {
        return $this->belongsToMany(GradeLevel::class, 'grade_level_subjects')
            ->withPivot('academic_year_id', 'sort_order')
            ->withTimestamps();
    }
}
