<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'is_elective',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_elective' => 'boolean',
    ];

    // Relationship: Grade levels that teach this subject
    public function gradeLevels()
    {
        return $this->belongsToMany(GradeLevel::class, 'grade_level_subjects')
                    ->withPivot('academic_year_id')
                    ->withTimestamps();
    }
}
