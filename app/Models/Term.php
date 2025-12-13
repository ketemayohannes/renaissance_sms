<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    protected $fillable = [
        'academic_year_id',
        'parent_term_id',
        'name',
        'type',
        'term_number',
        'start_date',
        'start_date',
        'end_date',
        'is_grading_open',
        'is_master_grading_open',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_grading_open' => 'boolean',
        'is_master_grading_open' => 'boolean',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // Relationship: A semester has many quarters
    public function quarters()
    {
        return $this->hasMany(Term::class, 'parent_term_id');
    }

    // Relationship: A quarter belongs to a semester
    public function semester()
    {
        return $this->belongsTo(Term::class, 'parent_term_id');
    }

    // Helper method to check if this is a quarter
    public function isQuarter()
    {
        return $this->type === 'quarter';
    }

    // Helper method to check if this is a semester
    public function isSemester()
    {
        return $this->type === 'semester';
    }

    // Get component quarters for a semester
    public function getComponentQuarters()
    {
        if ($this->isSemester()) {
            return $this->quarters;
        }
        return collect();
    }
}
