<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Student;
use App\Models\User;

use App\Traits\HasDivisionRestriction;

class Section extends Model
{
    use HasFactory, HasDivisionRestriction;

    protected $fillable = ['grade_level_id', 'academic_year_id', 'name', 'capacity', 'homeroom_teacher_id', 'is_active'];

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function homeroomTeacher()
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_enrollments')
                    ->withPivot('academic_year_id', 'enrollment_date', 'status')
                    ->withTimestamps();
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    /**
     * Scope a query to only include active sections.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
