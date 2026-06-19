<?php

namespace App\Models;

use App\Services\GradeCacheService;
use Illuminate\Database\Eloquent\Model;

class StudentEnrollment extends Model
{
    protected static function booted()
    {
        // Dashboard widget cache
        static::saved(fn()  => \Illuminate\Support\Facades\Cache::forget('admin_dashboard_students_by_grade'));
        static::deleted(fn() => \Illuminate\Support\Facades\Cache::forget('admin_dashboard_students_by_grade'));

        // Grade / roster cache — runs BEFORE save so we capture the original section_id
        static::updating(function (self $enrollment): void {
            // If the student is being transferred to a different section,
            // clear the OLD section's cache now (before the DB write).
            if ($enrollment->isDirty('section_id')) {
                $originalSectionId = $enrollment->getOriginal('section_id');
                if ($originalSectionId && $enrollment->academic_year_id) {
                    app(GradeCacheService::class)->clearSectionCache(
                        (int) $originalSectionId,
                        (int) $enrollment->academic_year_id
                    );
                }
            }
        });

        $bustGradeCache = function (self $enrollment): void {
            if ($enrollment->section_id && $enrollment->academic_year_id) {
                app(GradeCacheService::class)->clearSectionCache(
                    (int) $enrollment->section_id,
                    (int) $enrollment->academic_year_id
                );
            }
        };

        static::saved($bustGradeCache);
        static::deleted($bustGradeCache);
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

    /**
     * Get the student's roll number. If null, dynamically compute the alphabetical rank.
     */
    public function getRollNumberAttribute($value)
    {
        if ($value !== null) {
            return $value;
        }

        if (!$this->section_id || !$this->academic_year_id) {
            return null;
        }

        $rank = self::where('section_id', $this->section_id)
            ->where('academic_year_id', $this->academic_year_id)
            ->where('status', 'active')
            ->join('students', 'student_enrollments.student_id', '=', 'students.id')
            ->orderBy('students.first_name')
            ->orderBy('students.father_name')
            ->orderBy('students.grandfather_name')
            ->pluck('student_enrollments.student_id')
            ->search($this->student_id);

        return $rank !== false ? $rank + 1 : null;
    }

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
