<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentEnrollment extends Model
{
    protected static function booted()
    {
        static::saved(fn() => \Illuminate\Support\Facades\Cache::forget('admin_dashboard_students_by_grade'));
        static::deleted(fn() => \Illuminate\Support\Facades\Cache::forget('admin_dashboard_students_by_grade'));
    }

    protected $fillable = [
        'student_id',
        'section_id',
        'academic_year_id',
        'enrollment_date',
        'end_date',
        'status',
        'roll_number',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'end_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Recalculate roll numbers for a specific section and academic year.
     * Orders students alphabetically by First Name, Father Name, Grandfather Name.
     */
    public static function recalculateRollNumbers($sectionId, $academicYearId)
    {
        $enrollments = self::where('section_id', $sectionId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->join('students', 'student_enrollments.student_id', '=', 'students.id')
            ->orderBy('students.first_name')
            ->orderBy('students.father_name')
            ->orderBy('students.grandfather_name')
            ->select('student_enrollments.*') // Avoid column collision
            ->get();

        foreach ($enrollments as $index => $enrollment) {
            $enrollment->update(['roll_number' => $index + 1]);
        }
    }
}
